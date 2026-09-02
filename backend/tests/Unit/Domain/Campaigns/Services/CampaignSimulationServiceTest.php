<?php

namespace Tests\Unit\Domain\Campaigns\Services;

use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\CampaignDraftDTO;
use App\Domain\Campaigns\DTOs\MerchantProfileDTO;
use App\Domain\Campaigns\DTOs\PercentageDiscountParametersDTO;
use App\Domain\Campaigns\Enums\CampaignType;
use App\Domain\Campaigns\Exceptions\UnsupportedCampaignTypeException;
use App\Domain\Campaigns\Services\CampaignSimulationService;
use App\Domain\Campaigns\Services\CampaignSimulationStrategyFactory;
use App\Domain\Campaigns\Repositories\HistoricalDataRepository;
use App\Domain\Campaigns\Strategies\PercentageDiscountStrategy;
use PHPUnit\Framework\TestCase;

final class CampaignSimulationServiceTest extends TestCase
{
    public function test_it_delegates_percentage_discount_simulation_to_the_matching_strategy(): void
    {
        $factory = new CampaignSimulationStrategyFactory([
            CampaignType::PERCENTAGE_DISCOUNT->value => new PercentageDiscountStrategy(),
        ]);

        $result = $this->service($factory)
            ->simulate(new SimulationInputDTO(
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

    public function test_it_rejects_campaign_types_without_an_implemented_strategy(): void
    {
        $this->expectException(UnsupportedCampaignTypeException::class);

        $factory = new CampaignSimulationStrategyFactory([
            CampaignType::PERCENTAGE_DISCOUNT->value => new PercentageDiscountStrategy(),
        ]);

        $this->service($factory)
            ->simulate(new SimulationInputDTO(
                audienceSize: 1000,
                averageOrderValue: 100,
                grossMarginPercentage: 50,
                historicalConversionRate: 5,
                campaignConversionRate: 20,
                fixedCampaignCost: 0,
                parameters: new PercentageDiscountParametersDTO(0),
                campaignType: CampaignType::DOUBLE_POINTS,
            ));
    }

    public function test_it_completes_simulation_inputs_from_the_merchant_history(): void
    {
        $factory = new CampaignSimulationStrategyFactory([
            CampaignType::PERCENTAGE_DISCOUNT->value => new PercentageDiscountStrategy(),
        ]);

        $result = $this->service($factory)->simulateForMerchant(new CampaignDraftDTO(
            merchantId: 101,
            audienceSize: 1000,
            fixedCampaignCost: 0,
            parameters: new PercentageDiscountParametersDTO(10),
        ));

        self::assertSame(50.0, $result->baselineOrders);
        self::assertSame(60.0, $result->campaignOrders);
        self::assertSame(10.0, $result->incrementalOrders);
    }

    private function service(CampaignSimulationStrategyFactory $factory): CampaignSimulationService
    {
        return new CampaignSimulationService($factory, new class implements HistoricalDataRepository {
            public function getMerchantProfile(int $merchantId): MerchantProfileDTO
            {
                return new MerchantProfileDTO(
                    merchantId: $merchantId,
                    merchantName: 'Test Merchant',
                    currency: 'CAD',
                    averageOrderValue: 100,
                    grossMarginPercentage: 50,
                    historicalConversionRate: 5,
                    historicalCampaignLiftPercentage: 20,
                    pointsCostPerUnit: 0.01,
                    pointsRedemptionRate: 40,
                );
            }
        });
    }
}
