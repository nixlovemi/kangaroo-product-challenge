<?php

namespace App\Domain\Campaigns\DTOs;

final class DoublePointsParametersDTO implements CampaignParameters
{
    public function __construct(
        public readonly float $pointsMultiplier = 2,
    ) {
    }
}
