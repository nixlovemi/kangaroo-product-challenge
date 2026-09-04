<?php

namespace App\Domain\Recommendations\DTOs;

use App\Domain\Recommendations\Enums\RecommendationLever;
use App\Domain\Recommendations\Enums\RecommendationOutcome;

final class RecommendationDTO
{
    public function __construct(
        public readonly RecommendationLever $lever,
        public readonly RecommendationOutcome $outcome,
        public readonly string $message,
        public readonly float $currentValue,
        public readonly ?float $suggestedValue,
        public readonly ?float $projectedRoi,
    ) {
    }
}
