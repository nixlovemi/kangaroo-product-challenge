<?php

namespace App\Domain\Campaigns\Services;

use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;

final class CampaignSimulationService
{
    public function __construct(
        private readonly CampaignSimulationStrategyFactory $strategyFactory,
    ) {
    }

    public function simulate(SimulationInputDTO $input): SimulationResultDTO
    {
        $strategy = $this->strategyFactory->make($input->campaignType);

        return $strategy->simulate($input);
    }
}
