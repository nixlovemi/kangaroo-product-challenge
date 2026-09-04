<?php

namespace App\Domain\Recommendations\DTOs;

/**
 * Tunable thresholds for what counts as a good enough campaign and what counts as a
 * commercially real suggestion. Sourced from config so they can change without code edits.
 */
final class RecommendationGoalDTO
{
    /**
     * @param float[] $fixedCostProbePercentages
     * @param float[] $audienceProbeMultiples
     */
    public function __construct(
        public readonly float $targetRoiPercentage,
        public readonly float $minimumViableDiscountPercentage,
        public readonly float $minimumViablePointsMultiplier,
        public readonly array $fixedCostProbePercentages,
        public readonly array $audienceProbeMultiples,
        public readonly int $maximumAudienceSize,
    ) {
    }
}
