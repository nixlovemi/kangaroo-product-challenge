<?php

namespace App\Domain\Campaigns\Services;

use App\Domain\Campaigns\DTOs\CampaignDraftDTO;
use App\Domain\Campaigns\DTOs\CampaignScenarioAnalysisDTO;
use App\Domain\Campaigns\DTOs\MerchantOverviewDTO;
use App\Domain\Campaigns\DTOs\ScenarioSimulationResultDTO;
use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;
use App\Domain\Campaigns\Enums\ScenarioType;
use App\Domain\Campaigns\Repositories\HistoricalDataRepository;

final class CampaignSimulationService
{
    private const SCENARIO_RATE_PRECISION = 2;

    public function __construct(
        private readonly CampaignSimulationStrategyFactory $strategyFactory,
        private readonly HistoricalDataRepository $historicalDataRepository,
    ) {
    }

    public function simulate(SimulationInputDTO $input): SimulationResultDTO
    {
        $strategy = $this->strategyFactory->make($input->campaignType);

        return $strategy->simulate($input);
    }

    public function simulateForMerchant(CampaignDraftDTO $draft): SimulationResultDTO
    {
        $profile = $this->historicalDataRepository->getMerchantProfile($draft->merchantId);
        $campaignConversionRate = $this->expectedCampaignConversionRate($draft, $profile);

        return $this->simulateWithProfile($draft, $profile, $campaignConversionRate);
    }

    /**
     * Merchant profile plus the default (historical-lift-based) conversion rate a
     * campaign would use before the merchant overrides it with a custom estimate.
     */
    public function getMerchantOverview(int $merchantId): MerchantOverviewDTO
    {
        $profile = $this->historicalDataRepository->getMerchantProfile($merchantId);
        $expectedConversionRate = $this->calculateHistoricalCampaignConversionRate(
            $profile->historicalConversionRate,
            $profile->historicalCampaignLiftPercentage,
        );

        return new MerchantOverviewDTO($profile, round($expectedConversionRate, self::SCENARIO_RATE_PRECISION));
    }

    public function simulateScenariosForMerchant(CampaignDraftDTO $draft): CampaignScenarioAnalysisDTO
    {
        $profile = $this->historicalDataRepository->getMerchantProfile($draft->merchantId);
        $scenarios = [
            $this->simulateScenario($draft, $profile, ScenarioType::CONSERVATIVE),
            $this->simulateScenario($draft, $profile, ScenarioType::EXPECTED),
            $this->simulateScenario($draft, $profile, ScenarioType::STRONG_RESPONSE),
        ];

        if ($draft->campaignConversionRate !== null) {
            $scenarios[] = $this->simulateScenario(
                $draft,
                $profile,
                ScenarioType::CUSTOM,
                customConversionRate: $draft->campaignConversionRate,
            );
        }

        return new CampaignScenarioAnalysisDTO($profile, $scenarios, $draft->fixedCampaignCost);
    }

    private function simulateScenario(
        CampaignDraftDTO $draft,
        \App\Domain\Campaigns\DTOs\MerchantProfileDTO $profile,
        ScenarioType $scenarioType,
        ?float $customConversionRate = null,
    ): ScenarioSimulationResultDTO {
        $campaignConversionRate = $customConversionRate
            ?? $this->calculateHistoricalCampaignConversionRate(
                $profile->historicalConversionRate,
                $profile->historicalCampaignLiftPercentage * $scenarioType->historicalLiftMultiplier(),
            );
        $campaignConversionRate = round($campaignConversionRate, self::SCENARIO_RATE_PRECISION);

        return new ScenarioSimulationResultDTO(
            $scenarioType,
            $campaignConversionRate,
            $this->simulateWithProfile($draft, $profile, $campaignConversionRate),
        );
    }

    private function expectedCampaignConversionRate(CampaignDraftDTO $draft, \App\Domain\Campaigns\DTOs\MerchantProfileDTO $profile): float
    {
        return $draft->campaignConversionRate
            ?? $this->calculateHistoricalCampaignConversionRate($profile->historicalConversionRate, $profile->historicalCampaignLiftPercentage);
    }

    private function simulateWithProfile(
        CampaignDraftDTO $draft,
        \App\Domain\Campaigns\DTOs\MerchantProfileDTO $profile,
        float $campaignConversionRate,
    ): SimulationResultDTO {

        return $this->simulate(new SimulationInputDTO(
            audienceSize: $draft->audienceSize,
            averageOrderValue: $profile->averageOrderValue,
            grossMarginPercentage: $profile->grossMarginPercentage,
            historicalConversionRate: $profile->historicalConversionRate,
            campaignConversionRate: $campaignConversionRate,
            fixedCampaignCost: $draft->fixedCampaignCost,
            parameters: $draft->parameters,
            campaignType: $draft->campaignType,
            pointsEarnedPerCurrency: $profile->pointsEarnedPerCurrency,
            pointsCostPerUnit: $profile->pointsCostPerUnit,
            pointsRedemptionPercentage: $profile->pointsRedemptionPercentage,
        ));
    }

    private function calculateHistoricalCampaignConversionRate(float $historicalConversionRate, float $historicalLiftPercentage): float
    {
        return $historicalConversionRate * (1 + ($historicalLiftPercentage / 100));
    }
}
