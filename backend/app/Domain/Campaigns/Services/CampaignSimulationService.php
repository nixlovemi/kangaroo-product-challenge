<?php

namespace App\Domain\Campaigns\Services;

use App\Domain\Campaigns\DTOs\CampaignDraftDTO;
use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;
use App\Domain\Campaigns\Repositories\HistoricalDataRepository;

final class CampaignSimulationService
{
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
        $campaignConversionRate = $draft->campaignConversionRate
            ?? $this->calculateHistoricalCampaignConversionRate($profile->historicalConversionRate, $profile->historicalCampaignLiftPercentage);

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
