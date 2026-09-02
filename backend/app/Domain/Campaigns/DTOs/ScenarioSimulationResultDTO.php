<?php

namespace App\Domain\Campaigns\DTOs;

use App\Domain\Campaigns\Enums\ScenarioType;

final class ScenarioSimulationResultDTO
{
    public function __construct(
        public readonly ScenarioType $scenarioType,
        public readonly float $campaignConversionRate,
        public readonly SimulationResultDTO $result,
    ) {
    }
}
