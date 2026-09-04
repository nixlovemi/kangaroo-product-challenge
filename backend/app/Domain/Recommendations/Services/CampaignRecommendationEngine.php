<?php

namespace App\Domain\Recommendations\Services;

use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;
use App\Domain\Recommendations\DTOs\RecommendationDTO;
use App\Domain\Recommendations\DTOs\RecommendationGoalDTO;
use App\Domain\Recommendations\DTOs\RecommendationSetDTO;
use App\Domain\Recommendations\Levers\LeverAnalyzer;

/**
 * Runs every applicable lever against a simulated campaign and reports which changes would
 * reach the ROI target. Levers that cannot get there are still reported, because knowing a
 * campaign is structurally unprofitable is more useful to a merchant than a forced suggestion.
 */
final class CampaignRecommendationEngine implements CampaignRecommendationEngineInterface
{
    /**
     * @param LeverAnalyzer[] $analyzers
     */
    public function __construct(
        private readonly array $analyzers,
        private readonly SimulationMemo $memo,
    ) {
    }

    public function recommend(
        SimulationInputDTO $input,
        SimulationResultDTO $result,
        RecommendationGoalDTO $goal,
    ): RecommendationSetDTO {
        if ($result->roi !== null && $result->roi >= $goal->targetRoiPercentage) {
            return new RecommendationSetDTO(
                [],
                $goal->targetRoiPercentage,
                true,
                sprintf(
                    'This campaign already clears the %s ROI target, so no parameter changes are needed.',
                    $this->formatPercentage($goal->targetRoiPercentage),
                ),
            );
        }

        $recommendations = [];

        foreach ($this->analyzers as $analyzer) {
            if (!$analyzer->supports($input)) {
                continue;
            }

            $recommendation = $analyzer->analyze($input, $result, $goal, $this->memo);

            if ($recommendation !== null) {
                $recommendations[] = $recommendation;
            }
        }

        $recommendations = $this->actionableFirst($recommendations);

        return new RecommendationSetDTO(
            $recommendations,
            $goal->targetRoiPercentage,
            false,
            $this->summaryMessage($recommendations, $goal->targetRoiPercentage),
        );
    }

    /**
     * @param RecommendationDTO[] $recommendations
     * @return RecommendationDTO[]
     */
    private function actionableFirst(array $recommendations): array
    {
        usort(
            $recommendations,
            fn (RecommendationDTO $a, RecommendationDTO $b): int
                => (int) $b->outcome->isActionable() <=> (int) $a->outcome->isActionable(),
        );

        return $recommendations;
    }

    /**
     * @param RecommendationDTO[] $recommendations
     */
    private function summaryMessage(array $recommendations, float $targetRoiPercentage): string
    {
        $actionable = array_filter(
            $recommendations,
            fn (RecommendationDTO $recommendation): bool => $recommendation->outcome->isActionable(),
        );

        if ($actionable !== []) {
            return sprintf(
                'Any one of these changes would bring this campaign to the %s ROI target.',
                $this->formatPercentage($targetRoiPercentage),
            );
        }

        return sprintf(
            'No single parameter change reaches the %s ROI target. The incentive costs more than the margin it generates, so this campaign needs a different offer rather than a tweak.',
            $this->formatPercentage($targetRoiPercentage),
        );
    }

    private function formatPercentage(float $value): string
    {
        return number_format($value, 2) . '%';
    }
}
