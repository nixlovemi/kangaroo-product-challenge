<?php

namespace App\Domain\Recommendations\DTOs;

final class RecommendationSetDTO
{
    /**
     * @param RecommendationDTO[] $recommendations
     */
    public function __construct(
        public readonly array $recommendations,
        public readonly float $targetRoiPercentage,
        public readonly bool $alreadyMeetsTarget,
        public readonly string $summaryMessage,
    ) {
    }
}
