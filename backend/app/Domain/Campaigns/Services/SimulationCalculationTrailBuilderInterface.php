<?php

namespace App\Domain\Campaigns\Services;

use App\Domain\Campaigns\DTOs\CalculationStepDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;

interface SimulationCalculationTrailBuilderInterface
{
    /**
     * @return CalculationStepDTO[]
     */
    public function build(SimulationResultDTO $result): array;
}
