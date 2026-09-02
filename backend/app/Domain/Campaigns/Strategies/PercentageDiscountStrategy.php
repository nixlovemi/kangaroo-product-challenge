<?php

namespace App\Domain\Campaigns\Strategies;

use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\PercentageDiscountParametersDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;
use App\Domain\Campaigns\Exceptions\InvalidCampaignParametersException;
use App\Domain\Campaigns\Enums\HealthStatus;

final class PercentageDiscountStrategy implements CampaignSimulationStrategy
{
    private const DECIMAL_PRECISION = 2;
    private const HEALTHY_SAFETY_MULTIPLIER = 1.2;

    public function simulate(SimulationInputDTO $input): SimulationResultDTO
    {
        $parameters = $input->parameters;

        if (!$parameters instanceof PercentageDiscountParametersDTO) {
            throw InvalidCampaignParametersException::for($input->campaignType);
        }

        $marginRate = $this->percentageToRate($input->grossMarginPercentage);
        $historicalConversionRate = $this->percentageToRate($input->historicalConversionRate);
        $campaignConversionRate = $this->percentageToRate($input->campaignConversionRate);
        $discountRate = $this->percentageToRate($parameters->discountPercentage);

        $baselineOrders = $this->calculateOrders($input->audienceSize, $historicalConversionRate);
        $campaignOrders = $this->calculateOrders($input->audienceSize, $campaignConversionRate);
        $incrementalOrders = $this->calculateIncrementalOrders($campaignOrders, $baselineOrders);
        $contributionPerOrder = $this->calculateContributionPerOrder($input->averageOrderValue, $marginRate);
        $discountCost = $this->calculateDiscountCost($campaignOrders, $input->averageOrderValue, $discountRate);
        $incrementalRevenue = $this->calculateIncrementalRevenue($incrementalOrders, $input->averageOrderValue);
        $incrementalContribution = $this->calculateIncrementalContribution($incrementalOrders, $contributionPerOrder);
        $netImpact = $this->calculateNetImpact($incrementalContribution, $discountCost, $input->fixedCampaignCost);
        $breakEvenAchievable = $this->isBreakEvenAchievable(
            $contributionPerOrder,
            $input->averageOrderValue,
            $discountRate,
        );
        $breakEvenConversionRate = $this->calculateBreakEvenConversionRate(
            $input,
            $historicalConversionRate,
            $contributionPerOrder,
            $discountRate,
            $breakEvenAchievable,
        );
        $roi = $this->calculateRoi($netImpact, $discountCost, $input->fixedCampaignCost);

        return new SimulationResultDTO(
            baselineOrders: $this->round($baselineOrders),
            campaignOrders: $this->round($campaignOrders),
            incrementalOrders: $this->round($incrementalOrders),
            incrementalRevenue: $this->round($incrementalRevenue),
            discountCost: $this->round($discountCost),
            incrementalContribution: $this->round($incrementalContribution),
            netImpact: $this->round($netImpact),
            breakEvenConversionRate: $this->round($breakEvenConversionRate * 100),
            roi: $roi === null ? null : $this->round($roi),
            healthStatus: $this->healthStatus($netImpact, $campaignConversionRate, $breakEvenConversionRate),
            breakEvenAchievable: $breakEvenAchievable,
        );
    }

    private function percentageToRate(float $percentage): float
    {
        return $percentage / 100;
    }

    private function calculateOrders(int $audienceSize, float $conversionRate): float
    {
        return $audienceSize * $conversionRate;
    }

    private function calculateIncrementalOrders(float $campaignOrders, float $baselineOrders): float
    {
        return max($campaignOrders - $baselineOrders, 0);
    }

    private function calculateContributionPerOrder(float $averageOrderValue, float $marginRate): float
    {
        return $averageOrderValue * $marginRate;
    }

    private function calculateDiscountCost(float $campaignOrders, float $averageOrderValue, float $discountRate): float
    {
        return $campaignOrders * $averageOrderValue * $discountRate;
    }

    private function calculateIncrementalRevenue(float $incrementalOrders, float $averageOrderValue): float
    {
        return $incrementalOrders * $averageOrderValue;
    }

    private function calculateIncrementalContribution(float $incrementalOrders, float $contributionPerOrder): float
    {
        return $incrementalOrders * $contributionPerOrder;
    }

    private function calculateNetImpact(float $incrementalContribution, float $discountCost, float $fixedCampaignCost): float
    {
        return $incrementalContribution - $discountCost - $fixedCampaignCost;
    }

    private function isBreakEvenAchievable(
        float $contributionPerOrder,
        float $averageOrderValue,
        float $discountRate,
    ): bool {
        return $contributionPerOrder > $averageOrderValue * $discountRate;
    }

    private function calculateBreakEvenConversionRate(
        SimulationInputDTO $input,
        float $historicalConversionRate,
        float $contributionPerOrder,
        float $discountRate,
        bool $breakEvenAchievable,
    ): float {
        if (!$breakEvenAchievable) {
            return 1;
        }

        return (
            ($historicalConversionRate * $contributionPerOrder)
            + ($input->fixedCampaignCost / $input->audienceSize)
        ) / ($contributionPerOrder - ($input->averageOrderValue * $discountRate));
    }

    private function calculateRoi(float $netImpact, float $discountCost, float $fixedCampaignCost): ?float
    {
        $campaignCost = $discountCost + $fixedCampaignCost;

        return $campaignCost > 0 ? ($netImpact / $campaignCost) * 100 : null;
    }

    private function healthStatus(float $netImpact, float $campaignConversionRate, float $breakEvenConversionRate): HealthStatus
    {
        if ($netImpact > 0 && $campaignConversionRate >= $breakEvenConversionRate * self::HEALTHY_SAFETY_MULTIPLIER) {
            return HealthStatus::HEALTHY;
        }

        if ($netImpact >= 0 || $campaignConversionRate >= $breakEvenConversionRate) {
            return HealthStatus::CAUTION;
        }

        return HealthStatus::RISKY;
    }

    private function round(float $value): float
    {
        return round($value, self::DECIMAL_PRECISION);
    }
}
