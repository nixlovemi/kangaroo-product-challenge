<?php

namespace App\Domain\Campaigns\Strategies;

use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;
use App\Domain\Campaigns\Enums\HealthStatus;
use App\Domain\Campaigns\Services\SimulationInsightCalculator;
use App\Domain\Campaigns\Services\SimulationInsightCalculatorInterface;

abstract class AbstractCampaignSimulationStrategy implements CampaignSimulationStrategy
{
    protected const DECIMAL_PRECISION = 2;
    protected const HEALTHY_SAFETY_MULTIPLIER = 1.2;

    public function __construct(
        private readonly SimulationInsightCalculatorInterface $insightCalculator = new SimulationInsightCalculator(),
    ) {
    }

    protected function buildResult(
        SimulationInputDTO $input,
        float $incentiveCost,
        float $contributionPerOrder,
        float $breakEvenConversionRate,
        bool $breakEvenAchievable,
    ): SimulationResultDTO {
        $historicalConversionRate = $this->percentageToRate($input->historicalConversionRate);
        $campaignConversionRate = $this->percentageToRate($input->campaignConversionRate);
        $baselineOrders = $this->calculateOrders($input->audienceSize, $historicalConversionRate);
        $campaignOrders = $this->calculateOrders($input->audienceSize, $campaignConversionRate);
        $incrementalOrders = $this->calculateIncrementalOrders($campaignOrders, $baselineOrders);
        $incrementalRevenue = $this->calculateIncrementalRevenue($incrementalOrders, $input->averageOrderValue);
        $incrementalContribution = $this->calculateIncrementalContribution($incrementalOrders, $contributionPerOrder);
        $netImpact = $this->calculateNetImpact($incrementalContribution, $incentiveCost, $input->fixedCampaignCost);
        $campaignCost = $incentiveCost + $input->fixedCampaignCost;
        $roi = $campaignCost > 0 ? ($netImpact / $campaignCost) * 100 : null;
        $healthStatus = $this->healthStatus($netImpact, $campaignConversionRate, $breakEvenConversionRate);
        // Round once and reuse it everywhere below, so the insight text/numbers
        // always agree with the break_even_conversion_rate exposed in the DTO.
        $breakEvenConversionRatePercentage = $this->round($breakEvenConversionRate * 100);
        $insight = $this->insightCalculator->build(
            audienceSize: $input->audienceSize,
            baselineOrders: $baselineOrders,
            campaignOrders: $campaignOrders,
            incrementalOrders: $incrementalOrders,
            campaignConversionRate: $input->campaignConversionRate,
            breakEvenConversionRate: $breakEvenConversionRatePercentage,
            healthStatus: $healthStatus,
        );

        return new SimulationResultDTO(
            baselineOrders: $this->round($baselineOrders),
            campaignOrders: $this->round($campaignOrders),
            incrementalOrders: $this->round($incrementalOrders),
            incrementalRevenue: $this->round($incrementalRevenue),
            incentiveCost: $this->round($incentiveCost),
            incrementalContribution: $this->round($incrementalContribution),
            netImpact: $this->round($netImpact),
            breakEvenConversionRate: $breakEvenConversionRatePercentage,
            roi: $roi === null ? null : $this->round($roi),
            healthStatus: $healthStatus,
            breakEvenAchievable: $breakEvenAchievable,
            insight: $insight,
        );
    }

    protected function percentageToRate(float $percentage): float
    {
        return $percentage / 100;
    }

    protected function calculateOrders(int $audienceSize, float $conversionRate): float
    {
        return $audienceSize * $conversionRate;
    }

    protected function calculateIncrementalOrders(float $campaignOrders, float $baselineOrders): float
    {
        return max($campaignOrders - $baselineOrders, 0);
    }

    protected function calculateIncrementalRevenue(float $incrementalOrders, float $averageOrderValue): float
    {
        return $incrementalOrders * $averageOrderValue;
    }

    protected function calculateIncrementalContribution(float $incrementalOrders, float $contributionPerOrder): float
    {
        return $incrementalOrders * $contributionPerOrder;
    }

    protected function calculateNetImpact(float $incrementalContribution, float $incentiveCost, float $fixedCampaignCost): float
    {
        return $incrementalContribution - $incentiveCost - $fixedCampaignCost;
    }

    protected function healthStatus(float $netImpact, float $campaignConversionRate, float $breakEvenConversionRate): HealthStatus
    {
        if ($netImpact > 0 && $campaignConversionRate >= $breakEvenConversionRate * self::HEALTHY_SAFETY_MULTIPLIER) {
            return HealthStatus::HEALTHY;
        }

        if ($netImpact >= 0 || $campaignConversionRate >= $breakEvenConversionRate) {
            return HealthStatus::CAUTION;
        }

        return HealthStatus::RISKY;
    }

    protected function round(float $value): float
    {
        return round($value, self::DECIMAL_PRECISION);
    }
}
