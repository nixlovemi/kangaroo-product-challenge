<?php

namespace App\Domain\Recommendations\Levers;

use App\Domain\Campaigns\DTOs\DoublePointsParametersDTO;
use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;
use App\Domain\Recommendations\DTOs\RecommendationDTO;
use App\Domain\Recommendations\DTOs\RecommendationGoalDTO;
use App\Domain\Recommendations\Enums\RecommendationLever;
use App\Domain\Recommendations\Enums\RecommendationOutcome;
use App\Domain\Recommendations\Services\SimulationMemo;

final class PointsMultiplierLeverAnalyzer implements LeverAnalyzer
{
    use FormatsRecommendationValues;

    private const ROUNDING_STEP = 0.25;

    public function supports(SimulationInputDTO $input): bool
    {
        return $input->parameters instanceof DoublePointsParametersDTO;
    }

    public function analyze(
        SimulationInputDTO $input,
        SimulationResultDTO $result,
        RecommendationGoalDTO $goal,
        SimulationMemo $memo,
    ): ?RecommendationDTO {
        /** @var DoublePointsParametersDTO $parameters */
        $parameters = $input->parameters;
        $currentMultiplier = $parameters->pointsMultiplier;

        // At 1x there is no bonus and no incentive cost, so there is no slope to invert.
        if ($currentMultiplier <= 1 || $result->incentiveCost <= 0) {
            return null;
        }

        $required = $this->requiredMultiplier($result, $currentMultiplier, $goal->targetRoiPercentage);

        if ($required === null || $required <= 1) {
            return new RecommendationDTO(
                RecommendationLever::POINTS_MULTIPLIER,
                RecommendationOutcome::INFEASIBLE,
                'No points multiplier reaches the target: even with no bonus points at all, the campaign does not cover its costs.',
                $currentMultiplier,
                null,
                null,
            );
        }

        $suggested = floor($required / self::ROUNDING_STEP) * self::ROUNDING_STEP;

        if ($suggested < $goal->minimumViablePointsMultiplier) {
            return new RecommendationDTO(
                RecommendationLever::POINTS_MULTIPLIER,
                RecommendationOutcome::IMPLAUSIBLE,
                sprintf(
                    'Reaching the target would need the multiplier down at %.2fx, below the %.2fx that still motivates customers. The reward itself is too expensive for this margin.',
                    $required,
                    $goal->minimumViablePointsMultiplier,
                ),
                $currentMultiplier,
                null,
                null,
            );
        }

        if ($suggested >= $currentMultiplier) {
            return null;
        }

        $projected = $memo->simulate($this->withMultiplier($input, $suggested));

        return new RecommendationDTO(
            RecommendationLever::POINTS_MULTIPLIER,
            RecommendationOutcome::ACTIONABLE,
            sprintf(
                'Lower the multiplier from %.2fx to %.2fx to lift ROI to %s.',
                $currentMultiplier,
                $suggested,
                $this->formatPercentage($projected->roi ?? 0.0),
            ),
            $currentMultiplier,
            $suggested,
            $projected->roi,
        );
    }

    /**
     * Incentive cost is linear in the bonus portion of the multiplier, so the slope is read
     * off the current result as incentiveCost / (multiplier - 1) instead of re-deriving the
     * points formula here.
     */
    private function requiredMultiplier(
        SimulationResultDTO $result,
        float $currentMultiplier,
        float $targetRoiPercentage,
    ): ?float {
        $costPerBonusPoint = $result->incentiveCost / ($currentMultiplier - 1);

        if ($costPerBonusPoint <= 0) {
            return null;
        }

        $target = $targetRoiPercentage / 100;
        $denominator = 1 + $target;

        if ($denominator <= 0) {
            return null;
        }

        $bonus = ($result->incrementalContribution - ($result->fixedCampaignCost * $denominator))
            / ($costPerBonusPoint * $denominator);

        return 1 + $bonus;
    }

    private function withMultiplier(SimulationInputDTO $input, float $multiplier): SimulationInputDTO
    {
        return new SimulationInputDTO(
            audienceSize: $input->audienceSize,
            averageOrderValue: $input->averageOrderValue,
            grossMarginPercentage: $input->grossMarginPercentage,
            historicalConversionRate: $input->historicalConversionRate,
            campaignConversionRate: $input->campaignConversionRate,
            fixedCampaignCost: $input->fixedCampaignCost,
            parameters: new DoublePointsParametersDTO($multiplier),
            campaignType: $input->campaignType,
            pointsEarnedPerCurrency: $input->pointsEarnedPerCurrency,
            pointsCostPerUnit: $input->pointsCostPerUnit,
            pointsRedemptionPercentage: $input->pointsRedemptionPercentage,
        );
    }
}
