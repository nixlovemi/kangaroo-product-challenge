<?php

namespace App\Domain\Campaigns\DTOs;

final class CampaignScenarioAnalysisDTO
{
    /**
     * @param array<int, ScenarioSimulationResultDTO> $scenarios
     */
    public function __construct(
        public readonly MerchantProfileDTO $merchantProfile,
        public readonly array $scenarios,
    ) {
    }
}
