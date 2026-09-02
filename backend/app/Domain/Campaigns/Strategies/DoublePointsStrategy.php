<?php

namespace App\Domain\Campaigns\Strategies;

use App\Domain\Campaigns\DTOs\DoublePointsParametersDTO;
use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;
use App\Domain\Campaigns\Exceptions\InvalidCampaignParametersException;

final class DoublePointsStrategy extends AbstractCampaignSimulationStrategy
{
    public function simulate(SimulationInputDTO $input): SimulationResultDTO
    {
        $parameters = $input->parameters;

        if (!$parameters instanceof DoublePointsParametersDTO) {
            throw InvalidCampaignParametersException::for($input->campaignType);
        }

        if ($parameters->pointsMultiplier < 1) {
            throw InvalidCampaignParametersException::for($input->campaignType);
        }

        $historicalConversionRate = $this->percentageToRate($input->historicalConversionRate);
        $campaignConversionRate = $this->percentageToRate($input->campaignConversionRate);
        $campaignOrders = $this->calculateOrders($input->audienceSize, $campaignConversionRate);
        $incrementalPointsPerOrder = $this->calculateIncrementalPointsPerOrder($input, $parameters);
        $incentiveCost = $campaignOrders
            * $incrementalPointsPerOrder
            * $this->percentageToRate($input->pointsRedemptionPercentage)
            * $input->pointsCostPerUnit;
        $contributionPerOrder = $input->averageOrderValue
            * $this->percentageToRate($input->grossMarginPercentage);
        $incentiveCostPerOrder = $incrementalPointsPerOrder
            * $this->percentageToRate($input->pointsRedemptionPercentage)
            * $input->pointsCostPerUnit;
        $breakEvenAchievable = $contributionPerOrder > $incentiveCostPerOrder;
        $breakEvenConversionRate = $breakEvenAchievable
            ? ($historicalConversionRate * $contributionPerOrder + ($input->fixedCampaignCost / $input->audienceSize))
                / ($contributionPerOrder - $incentiveCostPerOrder)
            : 1;

        return $this->buildResult(
            $input,
            $incentiveCost,
            $contributionPerOrder,
            $breakEvenConversionRate,
            $breakEvenAchievable,
        );
    }

    private function calculateIncrementalPointsPerOrder(
        SimulationInputDTO $input,
        DoublePointsParametersDTO $parameters,
    ): float {
        $basePoints = $input->averageOrderValue * $input->pointsEarnedPerCurrency;

        return $basePoints * max($parameters->pointsMultiplier - 1, 0);
    }
}
