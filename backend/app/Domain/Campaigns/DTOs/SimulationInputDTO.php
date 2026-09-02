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
        public readonly float $discountPercentage,
        public readonly float $fixedCampaignCost,
        public readonly CampaignType $campaignType = CampaignType::PERCENTAGE_DISCOUNT,
    ) {
    }
}
