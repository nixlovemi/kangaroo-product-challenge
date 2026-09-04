<?php

namespace App\Domain\Campaigns\Services;

use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;

/**
 * The simulation capability other contexts are allowed to depend on.
 */
interface CampaignSimulatorInterface
{
    public function simulate(SimulationInputDTO $input): SimulationResultDTO;
}
