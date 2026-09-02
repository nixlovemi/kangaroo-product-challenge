<?php

namespace App\Domain\Campaigns\DTOs;

final class MerchantProfileDTO
{
    public function __construct(
        public readonly int $merchantId,
        public readonly string $merchantName,
        public readonly string $currency,
        public readonly float $averageOrderValue,
        public readonly float $grossMarginPercentage,
        public readonly float $historicalConversionRate,
        public readonly float $historicalCampaignLiftPercentage,
        public readonly float $pointsCostPerUnit,
        public readonly float $pointsRedemptionPercentage,
        public readonly float $pointsEarnedPerCurrency = 1,
    ) {
    }
}
