<?php

namespace App\Domain\Campaigns\Services;

use App\Domain\Campaigns\DTOs\CalculationStepDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;

/**
 * Rebuilds a line-by-line, plain-language audit trail of how a simulation reached
 * its numbers. Reads only SimulationResultDTO, so every current and future campaign
 * strategy gets the same explanation for free.
 */
final class SimulationCalculationTrailBuilder implements SimulationCalculationTrailBuilderInterface
{
    private const DECIMAL_PRECISION = 2;

    public function build(SimulationResultDTO $result): array
    {
        $campaignCost = $result->incentiveCost + $result->fixedCampaignCost;
        $contributionPerOrder = $this->safeDivide($result->incrementalContribution, $result->incrementalOrders);
        $grossMarginPercentage = $this->safeDivide($contributionPerOrder, $result->averageOrderValue) * 100;

        $steps = [
            new CalculationStepDTO(
                'Baseline orders',
                "Orders this audience places anyway, from the merchant's historical conversion rate.",
                $result->baselineOrders,
                'count',
            ),
            new CalculationStepDTO(
                'Campaign orders',
                'Total orders expected if the campaign converts at the projected rate.',
                $result->campaignOrders,
                'count',
            ),
            new CalculationStepDTO(
                'Incremental orders',
                sprintf(
                    '%s campaign orders − %s baseline orders',
                    $this->formatNumber($result->campaignOrders),
                    $this->formatNumber($result->baselineOrders),
                ),
                $result->incrementalOrders,
                'count',
            ),
            new CalculationStepDTO(
                'Incremental revenue',
                sprintf(
                    '%s incremental orders × %s average order value',
                    $this->formatNumber($result->incrementalOrders),
                    $this->formatNumber($result->averageOrderValue),
                ),
                $result->incrementalRevenue,
                'currency',
            ),
            new CalculationStepDTO(
                'Incremental contribution',
                sprintf(
                    '%s incremental orders × %s profit per order (%s gross margin on a %s order)',
                    $this->formatNumber($result->incrementalOrders),
                    $this->formatNumber($contributionPerOrder),
                    $this->formatPercentage($grossMarginPercentage),
                    $this->formatNumber($result->averageOrderValue),
                ),
                $result->incrementalContribution,
                'currency',
            ),
            new CalculationStepDTO(
                'Incentive cost',
                'What the discount or extra points cost across every campaign order.',
                $result->incentiveCost,
                'currency',
            ),
            new CalculationStepDTO(
                'Fixed campaign cost',
                'Flat cost of running the campaign, paid regardless of the response.',
                $result->fixedCampaignCost,
                'currency',
            ),
            new CalculationStepDTO(
                'Net impact',
                sprintf(
                    '%s contribution − %s incentive cost − %s fixed cost',
                    $this->formatNumber($result->incrementalContribution),
                    $this->formatNumber($result->incentiveCost),
                    $this->formatNumber($result->fixedCampaignCost),
                ),
                $result->netImpact,
                'currency',
            ),
            new CalculationStepDTO(
                'Break-even conversion',
                'Conversion rate the campaign needs for net impact to reach exactly zero.',
                $result->breakEvenConversionRate,
                'percentage',
            ),
        ];

        if ($result->roi !== null) {
            $steps[] = new CalculationStepDTO(
                'Return on investment',
                sprintf(
                    '%s net impact ÷ %s total campaign cost × 100',
                    $this->formatNumber($result->netImpact),
                    $this->formatNumber($campaignCost),
                ),
                $result->roi,
                'percentage',
            );
        }

        return $steps;
    }

    private function safeDivide(float $numerator, float $denominator): float
    {
        return $denominator > 0 ? round($numerator / $denominator, self::DECIMAL_PRECISION) : 0.0;
    }

    private function formatNumber(float $value): string
    {
        return number_format($value, self::DECIMAL_PRECISION);
    }

    private function formatPercentage(float $value): string
    {
        return number_format($value, self::DECIMAL_PRECISION) . '%';
    }
}
