<?php

namespace App\Domain\Recommendations\Levers;

use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;
use App\Domain\Recommendations\DTOs\RecommendationDTO;
use App\Domain\Recommendations\DTOs\RecommendationGoalDTO;
use App\Domain\Recommendations\Enums\RecommendationLever;
use App\Domain\Recommendations\Enums\RecommendationOutcome;
use App\Domain\Recommendations\Services\SimulationMemo;

/**
 * Probes realistic budget cuts rather than solving for an exact figure: "cut this by half"
 * is something a merchant can actually negotiate, "set it to $212.47" is not.
 */
final class FixedCostLeverAnalyzer implements LeverAnalyzer
{
    use FormatsRecommendationValues;

    public function supports(SimulationInputDTO $input): bool
    {
        return $input->fixedCampaignCost > 0;
    }

    public function analyze(
        SimulationInputDTO $input,
        SimulationResultDTO $result,
        RecommendationGoalDTO $goal,
        SimulationMemo $memo,
    ): ?RecommendationDTO {
        $currentCost = $input->fixedCampaignCost;

        foreach ($goal->fixedCostProbePercentages as $cutPercentage) {
            $probedCost = $currentCost * (1 - ($cutPercentage / 100));
            $projected = $memo->simulate($this->withFixedCost($input, $probedCost));

            if ($this->meetsTarget($projected->roi, $goal->targetRoiPercentage)) {
                return new RecommendationDTO(
                    RecommendationLever::FIXED_CAMPAIGN_COST,
                    RecommendationOutcome::ACTIONABLE,
                    sprintf(
                        'Cut the fixed campaign cost by %s, from %s to %s, to lift ROI to %s.',
                        $this->formatPercentage($cutPercentage),
                        $this->formatCurrency($currentCost),
                        $this->formatCurrency($probedCost),
                        $this->formatPercentage($projected->roi ?? 0.0),
                    ),
                    $currentCost,
                    round($probedCost, 2),
                    $projected->roi,
                );
            }
        }

        // If removing the cost entirely still misses the target, the fixed cost was never the problem.
        $withoutFixedCost = $memo->simulate($this->withFixedCost($input, 0.0));

        return new RecommendationDTO(
            RecommendationLever::FIXED_CAMPAIGN_COST,
            RecommendationOutcome::INFEASIBLE,
            sprintf(
                'Cutting the fixed cost will not rescue this campaign: even at zero it only reaches %s ROI, because the incentive already costs more than the margin it earns back.',
                $this->formatPercentage($withoutFixedCost->roi ?? 0.0),
            ),
            $currentCost,
            null,
            $withoutFixedCost->roi,
        );
    }

    private function withFixedCost(SimulationInputDTO $input, float $fixedCampaignCost): SimulationInputDTO
    {
        return new SimulationInputDTO(
            audienceSize: $input->audienceSize,
            averageOrderValue: $input->averageOrderValue,
            grossMarginPercentage: $input->grossMarginPercentage,
            historicalConversionRate: $input->historicalConversionRate,
            campaignConversionRate: $input->campaignConversionRate,
            fixedCampaignCost: $fixedCampaignCost,
            parameters: $input->parameters,
            campaignType: $input->campaignType,
            pointsEarnedPerCurrency: $input->pointsEarnedPerCurrency,
            pointsCostPerUnit: $input->pointsCostPerUnit,
            pointsRedemptionPercentage: $input->pointsRedemptionPercentage,
        );
    }
}
