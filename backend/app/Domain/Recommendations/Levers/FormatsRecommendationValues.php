<?php

namespace App\Domain\Recommendations\Levers;

/**
 * Shared helpers for turning simulation numbers into merchant-facing sentences.
 */
trait FormatsRecommendationValues
{
    protected function formatCurrency(float $value): string
    {
        return number_format($value, 2);
    }

    protected function formatPercentage(float $value): string
    {
        return number_format($value, 2) . '%';
    }

    protected function formatCount(float $value): string
    {
        return number_format($value, 0);
    }

    protected function meetsTarget(?float $roi, float $targetRoiPercentage): bool
    {
        return $roi !== null && $roi >= $targetRoiPercentage;
    }
}
