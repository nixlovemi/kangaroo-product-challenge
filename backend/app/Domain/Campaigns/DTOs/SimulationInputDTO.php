<?php

namespace App\Domain\Campaigns\DTOs;

use App\Domain\Campaigns\Enums\CampaignType;

final class SimulationInputDTO
{
    public function __construct(
        public readonly int $audienceSize,
        public readonly float $averageOrderValue,
        public readonly float $grossMarginPercentage,
        public readonly float $historicalConversionRate,
        public readonly float $campaignConversionRate,
        public readonly float $fixedCampaignCost,
        public readonly CampaignParameters $parameters,
        public readonly CampaignType $campaignType = CampaignType::PERCENTAGE_DISCOUNT,
        public readonly float $pointsEarnedPerCurrency = 1,
        public readonly float $pointsCostPerUnit = 0.01,
        public readonly float $pointsRedemptionPercentage = 40,
    ) {
    }
}
