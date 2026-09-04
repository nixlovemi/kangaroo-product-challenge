<?php

namespace App\Domain\Campaigns\Services;

use App\Domain\Campaigns\DTOs\SimulationInsightDTO;
use App\Domain\Campaigns\Enums\HealthStatus;

interface SimulationInsightCalculatorInterface
{
    public function build(
        int $audienceSize,
        float $baselineOrders,
        float $campaignOrders,
        float $incrementalOrders,
        float $campaignConversionRate,
        float $breakEvenConversionRate,
        HealthStatus $healthStatus,
    ): SimulationInsightDTO;
}
