<?php

namespace App\Domain\Campaigns\DTOs;

final class PercentageDiscountParametersDTO implements CampaignParameters
{
    public function __construct(
        public readonly float $discountPercentage,
    ) {
    }
}
