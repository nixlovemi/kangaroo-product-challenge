<?php

namespace App\Domain\Campaigns\Strategies;

use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\PercentageDiscountParametersDTO;
use App\Domain\Campaigns\Exceptions\InvalidCampaignParametersException;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;

final class PercentageDiscountStrategy extends AbstractCampaignSimulationStrategy
{
    public function simulate(SimulationInputDTO $input): SimulationResultDTO
    {
        $parameters = $input->parameters;

        if (!$parameters instanceof PercentageDiscountParametersDTO) {
            throw InvalidCampaignParametersException::for($input->campaignType);
        }

        $discountRate = $this->percentageToRate($parameters->discountPercentage);
        $marginRate = $this->percentageToRate($input->grossMarginPercentage);
        $historicalConversionRate = $this->percentageToRate($input->historicalConversionRate);
        $campaignOrders = $this->calculateOrders($input->audienceSize, $this->percentageToRate($input->campaignConversionRate));
        $incentiveCost = $this->calculateDiscountCost($campaignOrders, $input->averageOrderValue, $discountRate);
        $contributionPerOrder = $input->averageOrderValue * $marginRate;
        $breakEvenAchievable = $contributionPerOrder > $input->averageOrderValue * $discountRate;
        $breakEvenConversionRate = $breakEvenAchievable
            ? (($historicalConversionRate * $contributionPerOrder) + ($input->fixedCampaignCost / $input->audienceSize))
                / ($contributionPerOrder - ($input->averageOrderValue * $discountRate))
            : 1;

        return $this->buildResult(
            $input,
            $incentiveCost,
            $contributionPerOrder,
            $breakEvenConversionRate,
            $breakEvenAchievable,
        );
    }

    private function calculateDiscountCost(float $campaignOrders, float $averageOrderValue, float $discountRate): float
    {
        return $campaignOrders * $averageOrderValue * $discountRate;
    }
}
