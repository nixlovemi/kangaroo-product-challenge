<?php

namespace App\Domain\Recommendations\Levers;

use App\Domain\Campaigns\DTOs\PercentageDiscountParametersDTO;
use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;
use App\Domain\Recommendations\DTOs\RecommendationDTO;
use App\Domain\Recommendations\DTOs\RecommendationGoalDTO;
use App\Domain\Recommendations\Enums\RecommendationLever;
use App\Domain\Recommendations\Enums\RecommendationOutcome;
use App\Domain\Recommendations\Services\SimulationMemo;

final class DiscountLeverAnalyzer implements LeverAnalyzer
{
    use FormatsRecommendationValues;

    private const ROUNDING_STEP = 0.5;

    public function supports(SimulationInputDTO $input): bool
    {
        return $input->parameters instanceof PercentageDiscountParametersDTO;
    }

    public function analyze(
        SimulationInputDTO $input,
        SimulationResultDTO $result,
        RecommendationGoalDTO $goal,
        SimulationMemo $memo,
    ): ?RecommendationDTO {
        /** @var PercentageDiscountParametersDTO $parameters */
        $parameters = $input->parameters;
        $currentDiscount = $parameters->discountPercentage;

        if ($currentDiscount <= 0) {
            return null;
        }

        $required = $this->requiredDiscountPercentage($result, $goal->targetRoiPercentage);

        if ($required === null || $required <= 0) {
            return new RecommendationDTO(
                RecommendationLever::DISCOUNT_PERCENTAGE,
                RecommendationOutcome::INFEASIBLE,
                'No discount level reaches the target: even giving nothing away, the campaign does not generate enough margin to cover its costs.',
                $currentDiscount,
                null,
                null,
            );
        }

        // Round down so the suggested discount is safely under the break-even requirement.
        $suggested = floor($required / self::ROUNDING_STEP) * self::ROUNDING_STEP;

        if ($suggested < $goal->minimumViableDiscountPercentage) {
            return new RecommendationDTO(
                RecommendationLever::DISCOUNT_PERCENTAGE,
                RecommendationOutcome::IMPLAUSIBLE,
                sprintf(
                    'Reaching the target would need the discount down at %s, below the %s that still reads as a real offer. The incentive itself is the problem here, not its size.',
                    $this->formatPercentage($required),
                    $this->formatPercentage($goal->minimumViableDiscountPercentage),
                ),
                $currentDiscount,
                null,
                null,
            );
        }

        if ($suggested >= $currentDiscount) {
            return null;
        }

        $projected = $memo->simulate($this->withDiscount($input, $suggested));

        return new RecommendationDTO(
            RecommendationLever::DISCOUNT_PERCENTAGE,
            RecommendationOutcome::ACTIONABLE,
            sprintf(
                'Cut the discount from %s to %s to lift ROI to %s.',
                $this->formatPercentage($currentDiscount),
                $this->formatPercentage($suggested),
                $this->formatPercentage($projected->roi ?? 0.0),
            ),
            $currentDiscount,
            $suggested,
            $projected->roi,
        );
    }

    /**
     * Solves ROI(d) = target for the discount rate.
     *
     * With A = incremental contribution, F = fixed cost and B = campaign spend per unit of
     * discount, ROI(d) = (A - B*d - F) / (B*d + F), so d = [A - F(1+t)] / [B(1+t)].
     * B is inferred from the current result rather than re-deriving the strategy formula.
     */
    private function requiredDiscountPercentage(SimulationResultDTO $result, float $targetRoiPercentage): ?float
    {
        $spendPerDiscountPoint = $result->campaignOrders * $result->averageOrderValue;

        if ($spendPerDiscountPoint <= 0) {
            return null;
        }

        $target = $targetRoiPercentage / 100;
        $denominator = 1 + $target;

        if ($denominator <= 0) {
            return null;
        }

        $discountRate = ($result->incrementalContribution - ($result->fixedCampaignCost * $denominator))
            / ($spendPerDiscountPoint * $denominator);

        return $discountRate * 100;
    }

    private function withDiscount(SimulationInputDTO $input, float $discountPercentage): SimulationInputDTO
    {
        return new SimulationInputDTO(
            audienceSize: $input->audienceSize,
            averageOrderValue: $input->averageOrderValue,
            grossMarginPercentage: $input->grossMarginPercentage,
            historicalConversionRate: $input->historicalConversionRate,
            campaignConversionRate: $input->campaignConversionRate,
            fixedCampaignCost: $input->fixedCampaignCost,
            parameters: new PercentageDiscountParametersDTO($discountPercentage),
            campaignType: $input->campaignType,
            pointsEarnedPerCurrency: $input->pointsEarnedPerCurrency,
            pointsCostPerUnit: $input->pointsCostPerUnit,
            pointsRedemptionPercentage: $input->pointsRedemptionPercentage,
        );
    }
}
