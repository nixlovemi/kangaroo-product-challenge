<?php

namespace App\Domain\Recommendations\Services;

use App\Domain\Campaigns\DTOs\DoublePointsParametersDTO;
use App\Domain\Campaigns\DTOs\PercentageDiscountParametersDTO;
use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;
use App\Domain\Campaigns\Services\CampaignSimulatorInterface;

/**
 * Request-scoped memoization for the probe simulations the recommendation engine runs.
 *
 * Deliberately NOT a persistent cache: simulate() is pure in-memory arithmetic, so a
 * Redis/file round-trip would cost more than recomputing. This only avoids re-running
 * identical probes within a single request, and the memo dies with the request, so there
 * is nothing to invalidate.
 */
final class SimulationMemo
{
    /** @var array<string, SimulationResultDTO> */
    private array $results = [];

    public function __construct(
        private readonly CampaignSimulatorInterface $simulator,
    ) {
    }

    public function simulate(SimulationInputDTO $input): SimulationResultDTO
    {
        $key = $this->keyFor($input);

        return $this->results[$key] ??= $this->simulator->simulate($input);
    }

    private function keyFor(SimulationInputDTO $input): string
    {
        return implode('|', [
            $input->campaignType->value,
            $input->audienceSize,
            $input->averageOrderValue,
            $input->grossMarginPercentage,
            $input->historicalConversionRate,
            $input->campaignConversionRate,
            $input->fixedCampaignCost,
            $input->pointsEarnedPerCurrency,
            $input->pointsCostPerUnit,
            $input->pointsRedemptionPercentage,
            $this->parametersKey($input),
        ]);
    }

    private function parametersKey(SimulationInputDTO $input): string
    {
        $parameters = $input->parameters;

        return match (true) {
            $parameters instanceof PercentageDiscountParametersDTO => 'd:' . $parameters->discountPercentage,
            $parameters instanceof DoublePointsParametersDTO => 'p:' . $parameters->pointsMultiplier,
            default => 'unknown',
        };
    }
}
