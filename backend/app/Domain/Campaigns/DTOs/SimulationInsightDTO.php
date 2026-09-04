<?php

namespace App\Domain\Campaigns\DTOs;

final class SimulationInsightDTO
{
    public function __construct(
        public readonly float $breakEvenIncrementalOrders,
        public readonly float $breakEvenProgressPercentage,
        public readonly string $healthDriverMessage,
        public readonly string $actionMessage,
        public readonly string $ordersContextMessage,
    ) {
    }
}
