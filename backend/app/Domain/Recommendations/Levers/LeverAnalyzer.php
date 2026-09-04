<?php

namespace App\Domain\Recommendations\Levers;

use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;
use App\Domain\Recommendations\DTOs\RecommendationDTO;
use App\Domain\Recommendations\DTOs\RecommendationGoalDTO;
use App\Domain\Recommendations\Services\SimulationMemo;

interface LeverAnalyzer
{
    public function supports(SimulationInputDTO $input): bool;

    /**
     * Returns null when this lever has nothing meaningful to say for the given campaign.
     */
    public function analyze(
        SimulationInputDTO $input,
        SimulationResultDTO $result,
        RecommendationGoalDTO $goal,
        SimulationMemo $memo,
    ): ?RecommendationDTO;
}
