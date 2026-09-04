<?php

namespace App\Domain\Recommendations\DTOs;

use App\Domain\Campaigns\DTOs\CampaignScenarioAnalysisDTO;

final class CampaignAdviceDTO
{
    /**
     * @param ScenarioAdviceDTO[] $scenarioAdvice
     */
    public function __construct(
        public readonly CampaignScenarioAnalysisDTO $analysis,
        public readonly array $scenarioAdvice,
    ) {
    }
}
