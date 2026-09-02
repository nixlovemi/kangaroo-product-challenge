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
        public readonly float $discountCost,
        public readonly float $incrementalContribution,
        public readonly float $netImpact,
        public readonly float $breakEvenConversionRate,
        public readonly ?float $roi,
        public readonly HealthStatus $healthStatus,
        public readonly bool $breakEvenAchievable,
    ) {
    }
}
