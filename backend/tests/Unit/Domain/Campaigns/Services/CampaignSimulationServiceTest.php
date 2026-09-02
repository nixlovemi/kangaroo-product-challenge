<?php

namespace Tests\Unit\Domain\Campaigns\Services;

use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\Enums\CampaignType;
use App\Domain\Campaigns\Exceptions\UnsupportedCampaignTypeException;
use App\Domain\Campaigns\Services\CampaignSimulationService;
use App\Domain\Campaigns\Services\CampaignSimulationStrategyFactory;
use App\Domain\Campaigns\Strategies\PercentageDiscountStrategy;
use PHPUnit\Framework\TestCase;

final class CampaignSimulationServiceTest extends TestCase
{
    public function test_it_delegates_percentage_discount_simulation_to_the_matching_strategy(): void
    {
        $factory = new CampaignSimulationStrategyFactory([
            CampaignType::PERCENTAGE_DISCOUNT->value => new PercentageDiscountStrategy(),
        ]);

        $result = (new CampaignSimulationService($factory))
            ->simulate(new SimulationInputDTO(
                audienceSize: 1000,
                averageOrderValue: 100,
                grossMarginPercentage: 50,
                historicalConversionRate: 5,
                campaignConversionRate: 20,
                discountPercentage: 10,
                fixedCampaignCost: 0,
            ));

        self::assertSame(5500.0, $result->netImpact);
    }

    public function test_it_rejects_campaign_types_without_an_implemented_strategy(): void
    {
        $this->expectException(UnsupportedCampaignTypeException::class);

        $factory = new CampaignSimulationStrategyFactory([
            CampaignType::PERCENTAGE_DISCOUNT->value => new PercentageDiscountStrategy(),
        ]);

        (new CampaignSimulationService($factory))
            ->simulate(new SimulationInputDTO(
                audienceSize: 1000,
                averageOrderValue: 100,
                grossMarginPercentage: 50,
                historicalConversionRate: 5,
                campaignConversionRate: 20,
                discountPercentage: 0,
                fixedCampaignCost: 0,
                campaignType: CampaignType::DOUBLE_POINTS,
            ));
    }
}
