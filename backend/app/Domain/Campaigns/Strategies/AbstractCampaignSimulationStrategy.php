<?php

namespace App\Domain\Campaigns\Strategies;

use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;
use App\Domain\Campaigns\Enums\HealthStatus;

abstract class AbstractCampaignSimulationStrategy implements CampaignSimulationStrategy
{
    protected const DECIMAL_PRECISION = 2;
    protected const HEALTHY_SAFETY_MULTIPLIER = 1.2;

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

        return new SimulationResultDTO(
            baselineOrders: $this->round($baselineOrders),
            campaignOrders: $this->round($campaignOrders),
            incrementalOrders: $this->round($incrementalOrders),
            incrementalRevenue: $this->round($incrementalRevenue),
            incentiveCost: $this->round($incentiveCost),
            incrementalContribution: $this->round($incrementalContribution),
            netImpact: $this->round($netImpact),
            breakEvenConversionRate: $this->round($breakEvenConversionRate * 100),
            roi: $roi === null ? null : $this->round($roi),
            healthStatus: $this->healthStatus($netImpact, $campaignConversionRate, $breakEvenConversionRate),
            breakEvenAchievable: $breakEvenAchievable,
        );
    }

    protected function percentageToRate(float $percentage): float
    {
        return $this->round($percentage / 100);
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
