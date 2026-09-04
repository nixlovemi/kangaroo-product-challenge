<?php

namespace Tests\Unit\Domain\Recommendations;

use App\Domain\Campaigns\DTOs\CampaignParameters;
use App\Domain\Campaigns\DTOs\PercentageDiscountParametersDTO;
use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationInsightDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;
use App\Domain\Campaigns\Enums\CampaignType;
use App\Domain\Campaigns\Enums\HealthStatus;
use App\Domain\Recommendations\DTOs\RecommendationGoalDTO;

trait BuildsRecommendationFixtures
{
    protected function goal(
        float $targetRoiPercentage = -5.0,
        float $minimumViableDiscountPercentage = 5.0,
        float $minimumViablePointsMultiplier = 1.25,
    ): RecommendationGoalDTO {
        return new RecommendationGoalDTO(
            targetRoiPercentage: $targetRoiPercentage,
            minimumViableDiscountPercentage: $minimumViableDiscountPercentage,
            minimumViablePointsMultiplier: $minimumViablePointsMultiplier,
            fixedCostProbePercentages: [50.0, 75.0],
            audienceProbeMultiples: [1.5, 2.0],
            maximumAudienceSize: 1000000,
        );
    }

    protected function input(
        ?CampaignParameters $parameters = null,
        int $audienceSize = 1200,
        float $fixedCampaignCost = 250,
        float $grossMarginPercentage = 34,
        CampaignType $campaignType = CampaignType::PERCENTAGE_DISCOUNT,
    ): SimulationInputDTO {
        return new SimulationInputDTO(
            audienceSize: $audienceSize,
            averageOrderValue: 112,
            grossMarginPercentage: $grossMarginPercentage,
            historicalConversionRate: 3.1,
            campaignConversionRate: 3.66,
            fixedCampaignCost: $fixedCampaignCost,
            parameters: $parameters ?? new PercentageDiscountParametersDTO(10),
            campaignType: $campaignType,
        );
    }

    protected function simulationResult(
        float $incrementalContribution = 255.90,
        float $incentiveCost = 491.90,
        float $fixedCampaignCost = 250,
        ?float $roi = -65.51,
        float $campaignOrders = 43.92,
        int $audienceSize = 1200,
    ): SimulationResultDTO {
        return new SimulationResultDTO(
            baselineOrders: 37.2,
            campaignOrders: $campaignOrders,
            incrementalOrders: 6.72,
            incrementalRevenue: 752.64,
            incentiveCost: $incentiveCost,
            incrementalContribution: $incrementalContribution,
            netImpact: $incrementalContribution - $incentiveCost - $fixedCampaignCost,
            breakEvenConversionRate: 5.17,
            roi: $roi,
            healthStatus: HealthStatus::RISKY,
            breakEvenAchievable: true,
            insight: new SimulationInsightDTO(0, 0, 'driver', 'action', 'orders'),
            fixedCampaignCost: $fixedCampaignCost,
            averageOrderValue: 112,
            audienceSize: $audienceSize,
        );
    }
}

