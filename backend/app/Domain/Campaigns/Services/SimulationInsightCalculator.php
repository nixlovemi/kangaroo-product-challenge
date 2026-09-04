<?php

namespace App\Domain\Campaigns\Services;

use App\Domain\Campaigns\DTOs\SimulationInsightDTO;
use App\Domain\Campaigns\Enums\HealthStatus;

/**
 * Turns raw simulation numbers into merchant-facing explanations (why the health
 * status is what it is, and what it takes to reach break-even). Kept separate from
 * the strategies so the math stays free of presentation text.
 *
 * Convention: every parameter/property named "Rate" or "Percentage" is on a 0-100
 * percentage scale (e.g. 6.82 for 6.82%), never a 0-1 fraction. Division by the
 * audience size is the only place that scale is converted, and only there.
 */
final class SimulationInsightCalculator implements SimulationInsightCalculatorInterface
{
    private const DECIMAL_PRECISION = 2;

    public function build(
        int $audienceSize,
        float $baselineOrders,
        float $campaignOrders,
        float $incrementalOrders,
        float $campaignConversionRate,
        float $breakEvenConversionRate,
        HealthStatus $healthStatus,
    ): SimulationInsightDTO {
        // Kept unrounded until the DTO is built, so every consumer of this raw
        // value (below) and the value exposed to the API agree with each other.
        $breakEvenIncrementalOrders = $this->breakEvenIncrementalOrders($audienceSize, $baselineOrders, $breakEvenConversionRate);

        return new SimulationInsightDTO(
            breakEvenIncrementalOrders: round($breakEvenIncrementalOrders, self::DECIMAL_PRECISION),
            breakEvenProgressPercentage: round($this->breakEvenProgressPercentage($campaignConversionRate, $breakEvenConversionRate), self::DECIMAL_PRECISION),
            healthDriverMessage: $this->healthDriverMessage($healthStatus, $campaignConversionRate, $breakEvenConversionRate),
            actionMessage: $this->actionMessage($healthStatus, $breakEvenConversionRate, $campaignConversionRate, $breakEvenIncrementalOrders, $incrementalOrders),
            ordersContextMessage: $this->ordersContextMessage($baselineOrders, $campaignOrders),
        );
    }

    private function breakEvenIncrementalOrders(int $audienceSize, float $baselineOrders, float $breakEvenConversionRate): float
    {
        $breakEvenTotalOrders = ($breakEvenConversionRate / 100) * $audienceSize;

        return max(0, $breakEvenTotalOrders - $baselineOrders);
    }

    private function breakEvenProgressPercentage(float $campaignConversionRate, float $breakEvenConversionRate): float
    {
        if ($breakEvenConversionRate <= 0) {
            return 0;
        }

        return min(100, max(0, ($campaignConversionRate / $breakEvenConversionRate) * 100));
    }

    private function healthDriverMessage(HealthStatus $healthStatus, float $campaignConversionRate, float $breakEvenConversionRate): string
    {
        $projected = $this->formatPercentage($campaignConversionRate);
        $breakEven = $this->formatPercentage($breakEvenConversionRate);

        return match ($healthStatus) {
            HealthStatus::RISKY => "Risky because the projected conversion ({$projected}) is below the {$breakEven} needed to cover incentive and campaign costs.",
            HealthStatus::CAUTION => "Projected to break even, but with little safety margin above the {$breakEven} conversion needed to cover costs.",
            HealthStatus::HEALTHY => "Healthy: projected conversion ({$projected}) is comfortably above the {$breakEven} break-even threshold.",
        };
    }

    /**
     * Mirrors the HealthStatus enum exactly: RISKY is the only status where the
     * merchant is still short of break-even, so it's the only branch that needs
     * a numeric gap to close.
     */
    private function actionMessage(
        HealthStatus $healthStatus,
        float $breakEvenConversionRate,
        float $campaignConversionRate,
        float $breakEvenIncrementalOrders,
        float $incrementalOrders,
    ): string {
        if ($healthStatus === HealthStatus::HEALTHY) {
            return 'Already past break-even with room to spare — no changes needed to stay profitable.';
        }

        if ($healthStatus === HealthStatus::CAUTION) {
            return 'At break-even, but with little safety margin — a small drop in response could make this campaign unprofitable.';
        }

        $rateGap = round($breakEvenConversionRate - $campaignConversionRate, self::DECIMAL_PRECISION);
        $ordersGap = max(0, round($breakEvenIncrementalOrders - $incrementalOrders));

        return sprintf(
            'Needs %d more incremental orders (+%.2fpp conversion) to break even.',
            $ordersGap,
            $rateGap,
        );
    }

    private function ordersContextMessage(float $baselineOrders, float $campaignOrders): string
    {
        return sprintf(
            '%d would order anyway · %d total with this campaign',
            (int) round($baselineOrders),
            (int) round($campaignOrders),
        );
    }

    private function formatPercentage(float $value): string
    {
        return number_format($value, 2) . '%';
    }
}
