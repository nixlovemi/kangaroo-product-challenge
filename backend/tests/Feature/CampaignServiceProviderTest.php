<?php

namespace Tests\Feature;

use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\CampaignDraftDTO;
use App\Domain\Campaigns\DTOs\DoublePointsParametersDTO;
use App\Domain\Campaigns\Enums\CampaignType;
use App\Domain\Campaigns\DTOs\PercentageDiscountParametersDTO;
use App\Domain\Campaigns\Services\CampaignSimulationService;
use Tests\TestCase;

final class CampaignServiceProviderTest extends TestCase
{
    public function test_the_campaign_simulation_service_is_resolved_from_the_container(): void
    {
        $service = $this->app->make(CampaignSimulationService::class);

        $result = $service->simulate(new SimulationInputDTO(
            audienceSize: 1000,
            averageOrderValue: 100,
            grossMarginPercentage: 50,
            historicalConversionRate: 5,
            campaignConversionRate: 20,
            fixedCampaignCost: 0,
            parameters: new PercentageDiscountParametersDTO(10),
        ));

        self::assertSame(5500.0, $result->netImpact);
    }

    public function test_the_container_supports_double_points_with_merchant_history(): void
    {
        $result = $this->app->make(CampaignSimulationService::class)->simulateForMerchant(new CampaignDraftDTO(
            merchantId: 101,
            audienceSize: 1000,
            fixedCampaignCost: 0,
            parameters: new DoublePointsParametersDTO(),
            campaignType: CampaignType::DOUBLE_POINTS,
        ));

        self::assertSame(177.42, $result->incentiveCost);
        self::assertSame(623.54, $result->netImpact);
    }
}
