<?php

namespace App\Domain\Campaigns\Strategies;

use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;

interface CampaignSimulationStrategy
{
    public function simulate(SimulationInputDTO $input): SimulationResultDTO;
}
