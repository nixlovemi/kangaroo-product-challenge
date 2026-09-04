<?php

namespace App\Domain\Recommendations\DTOs;

use App\Domain\Campaigns\DTOs\ScenarioSimulationResultDTO;

final class ScenarioAdviceDTO
{
    public function __construct(
        public readonly ScenarioSimulationResultDTO $scenario,
        public readonly RecommendationSetDTO $recommendations,
    ) {
    }
}
