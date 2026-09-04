<?php

namespace App\Domain\Recommendations\Levers;

use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;
use App\Domain\Recommendations\DTOs\RecommendationDTO;
use App\Domain\Recommendations\DTOs\RecommendationGoalDTO;
use App\Domain\Recommendations\Enums\RecommendationLever;
use App\Domain\Recommendations\Enums\RecommendationOutcome;
use App\Domain\Recommendations\Services\SimulationMemo;

final class AudienceLeverAnalyzer implements LeverAnalyzer
{
    use FormatsRecommendationValues;

    public function supports(SimulationInputDTO $input): bool
    {
        return $input->audienceSize > 0;
    }

    public function analyze(
        SimulationInputDTO $input,
        SimulationResultDTO $result,
        RecommendationGoalDTO $goal,
        SimulationMemo $memo,
    ): ?RecommendationDTO {
        $currentAudience = $input->audienceSize;

        // Scaling only dilutes the fixed cost when each extra customer earns more margin
        // than the incentive costs. Otherwise a bigger audience just multiplies the loss.
        if ($result->incrementalContribution <= $result->incentiveCost) {
            return new RecommendationDTO(
                RecommendationLever::AUDIENCE_SIZE,
                RecommendationOutcome::INFEASIBLE,
                'Do not scale this campaign up: every additional customer costs more in incentive than the margin they bring, so a bigger audience multiplies the loss instead of diluting the fixed cost.',
                $currentAudience,
                null,
                null,
            );
        }

        foreach ($goal->audienceProbeMultiples as $multiple) {
            $probedAudience = (int) round($currentAudience * $multiple);

            if ($probedAudience > $goal->maximumAudienceSize) {
                continue;
            }

            $projected = $memo->simulate($this->withAudience($input, $probedAudience));

            if ($this->meetsTarget($projected->roi, $goal->targetRoiPercentage)) {
                return new RecommendationDTO(
                    RecommendationLever::AUDIENCE_SIZE,
                    RecommendationOutcome::ACTIONABLE,
                    sprintf(
                        'Widen the audience from %s to %s people to spread the fixed cost further and lift ROI to %s.',
                        $this->formatCount($currentAudience),
                        $this->formatCount($probedAudience),
                        $this->formatPercentage($projected->roi ?? 0.0),
                    ),
                    $currentAudience,
                    $probedAudience,
                    $projected->roi,
                );
            }
        }

        return new RecommendationDTO(
            RecommendationLever::AUDIENCE_SIZE,
            RecommendationOutcome::INFEASIBLE,
            'Widening the audience helps, but not enough on its own to reach the target within a realistic segment size.',
            $currentAudience,
            null,
            null,
        );
    }

    private function withAudience(SimulationInputDTO $input, int $audienceSize): SimulationInputDTO
    {
        return new SimulationInputDTO(
            audienceSize: $audienceSize,
            averageOrderValue: $input->averageOrderValue,
            grossMarginPercentage: $input->grossMarginPercentage,
            historicalConversionRate: $input->historicalConversionRate,
            campaignConversionRate: $input->campaignConversionRate,
            fixedCampaignCost: $input->fixedCampaignCost,
            parameters: $input->parameters,
            campaignType: $input->campaignType,
            pointsEarnedPerCurrency: $input->pointsEarnedPerCurrency,
            pointsCostPerUnit: $input->pointsCostPerUnit,
            pointsRedemptionPercentage: $input->pointsRedemptionPercentage,
        );
    }
}
