<?php

namespace App\Domain\Campaigns\Services;

use App\Domain\Campaigns\Enums\CampaignType;
use App\Domain\Campaigns\Exceptions\UnsupportedCampaignTypeException;
use App\Domain\Campaigns\Strategies\CampaignSimulationStrategy;

final class CampaignSimulationStrategyFactory
{
    /**
     * @param array<string, CampaignSimulationStrategy> $strategies
     */
    public function __construct(
        private readonly array $strategies,
    ) {
    }

    public function make(CampaignType $campaignType): CampaignSimulationStrategy
    {
        return $this->strategies[$campaignType->value]
            ?? throw UnsupportedCampaignTypeException::for($campaignType);
    }
}
