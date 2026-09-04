<?php

namespace App\Domain\Recommendations\Services;

use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;
use App\Domain\Recommendations\DTOs\RecommendationGoalDTO;
use App\Domain\Recommendations\DTOs\RecommendationSetDTO;

interface CampaignRecommendationEngineInterface
{
    public function recommend(
        SimulationInputDTO $input,
        SimulationResultDTO $result,
        RecommendationGoalDTO $goal,
    ): RecommendationSetDTO;
}
