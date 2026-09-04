<?php

namespace App\Domain\Campaigns\DTOs;

use App\Domain\Campaigns\Enums\HealthStatus;

final class SimulationResultDTO
{
    public function __construct(
        public readonly float $baselineOrders,
        public readonly float $campaignOrders,
        public readonly float $incrementalOrders,
        public readonly float $incrementalRevenue,
        public readonly float $incentiveCost,
        public readonly float $incrementalContribution,
        public readonly float $netImpact,
        public readonly float $breakEvenConversionRate,
        public readonly ?float $roi,
        public readonly HealthStatus $healthStatus,
        public readonly bool $breakEvenAchievable,
        public readonly SimulationInsightDTO $insight,
        // Campaign inputs echoed back so the calculation trail can be rebuilt
        // from this DTO alone, for any strategy.
        public readonly float $fixedCampaignCost,
        public readonly float $averageOrderValue,
    ) {
    }
}
