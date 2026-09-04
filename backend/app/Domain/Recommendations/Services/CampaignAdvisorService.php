<?php

namespace App\Domain\Recommendations\Services;

use App\Domain\Campaigns\DTOs\CampaignDraftDTO;
use App\Domain\Campaigns\DTOs\CampaignScenarioAnalysisDTO;
use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\Services\CampaignSimulationService;
use App\Domain\Recommendations\DTOs\CampaignAdviceDTO;
use App\Domain\Recommendations\DTOs\RecommendationGoalDTO;
use App\Domain\Recommendations\DTOs\ScenarioAdviceDTO;

/**
 * Port of the recommendations context: runs a campaign analysis through Campaigns, then
 * enriches each scenario with the changes that would make it viable. Campaigns has no
 * knowledge of this service, keeping the dependency one-way.
 */
final class CampaignAdvisorService
{
    public function __construct(
        private readonly CampaignSimulationService $simulationService,
        private readonly CampaignRecommendationEngineInterface $recommendationEngine,
        private readonly RecommendationGoalDTO $goal,
    ) {
    }

    public function analyzeScenarios(CampaignDraftDTO $draft): CampaignAdviceDTO
    {
        $analysis = $this->simulationService->simulateScenariosForMerchant($draft);

        $advice = array_map(
            fn ($scenario): ScenarioAdviceDTO => new ScenarioAdviceDTO(
                $scenario,
                $this->recommendationEngine->recommend(
                    $this->inputFor($draft, $analysis, $scenario->campaignConversionRate),
                    $scenario->result,
                    $this->goal,
                ),
            ),
            $analysis->scenarios,
        );

        return new CampaignAdviceDTO($analysis, $advice);
    }

    private function inputFor(
        CampaignDraftDTO $draft,
        CampaignScenarioAnalysisDTO $analysis,
        float $campaignConversionRate,
    ): SimulationInputDTO {
        $profile = $analysis->merchantProfile;

        return new SimulationInputDTO(
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
        );
    }
}
