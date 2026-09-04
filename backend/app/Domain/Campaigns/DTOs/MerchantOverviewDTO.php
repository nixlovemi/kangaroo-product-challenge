<?php

namespace App\Domain\Campaigns\DTOs;

final class MerchantOverviewDTO
{
    public function __construct(
        public readonly MerchantProfileDTO $merchantProfile,
        public readonly float $expectedConversionRate,
    ) {
    }
}
