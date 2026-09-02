<?php

namespace Tests\Unit\Domain\Campaigns\Strategies;

use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\DoublePointsParametersDTO;
use App\Domain\Campaigns\DTOs\PercentageDiscountParametersDTO;
use App\Domain\Campaigns\Enums\CampaignType;
use App\Domain\Campaigns\Exceptions\InvalidCampaignParametersException;
use App\Domain\Campaigns\Enums\HealthStatus;
use App\Domain\Campaigns\Strategies\PercentageDiscountStrategy;
use PHPUnit\Framework\TestCase;

final class PercentageDiscountStrategyTest extends TestCase
{
    public function test_it_rejects_parameters_from_another_campaign_type(): void
    {
        $this->expectException(InvalidCampaignParametersException::class);

        (new PercentageDiscountStrategy())->simulate(new SimulationInputDTO(
            audienceSize: 1000,
            averageOrderValue: 100,
            grossMarginPercentage: 50,
            historicalConversionRate: 5,
            campaignConversionRate: 10,
            fixedCampaignCost: 0,
            parameters: new DoublePointsParametersDTO(),
        ));
    }

    public function test_it_separates_baseline_and_incremental_orders(): void
    {
        $result = (new PercentageDiscountStrategy())->simulate(new SimulationInputDTO(
            audienceSize: 1200,
            averageOrderValue: 75,
            grossMarginPercentage: 45,
            historicalConversionRate: 4.2,
            campaignConversionRate: 6.5,
            fixedCampaignCost: 250,
            parameters: new PercentageDiscountParametersDTO(15),
            campaignType: CampaignType::PERCENTAGE_DISCOUNT,
        ));

        self::assertSame(50.4, $result->baselineOrders);
        self::assertSame(78.0, $result->campaignOrders);
        self::assertSame(27.6, $result->incrementalOrders);
        self::assertSame(931.5, $result->incrementalContribution);
        self::assertSame(-196.0, $result->netImpact);
        self::assertSame(7.23, $result->breakEvenConversionRate);
        self::assertSame(HealthStatus::RISKY, $result->healthStatus);
    }

    public function test_it_marks_break_even_as_unachievable_when_discount_reaches_margin(): void
    {
        $result = (new PercentageDiscountStrategy())->simulate(new SimulationInputDTO(
            audienceSize: 1000,
            averageOrderValue: 50,
            grossMarginPercentage: 20,
            historicalConversionRate: 5,
            campaignConversionRate: 10,
            fixedCampaignCost: 100,
            parameters: new PercentageDiscountParametersDTO(20),
        ));

        self::assertFalse($result->breakEvenAchievable);
        self::assertSame(100.0, $result->breakEvenConversionRate);
        self::assertSame(HealthStatus::RISKY, $result->healthStatus);
    }

    public function test_it_marks_a_profitable_campaign_with_a_safety_margin_as_healthy(): void
    {
        $result = (new PercentageDiscountStrategy())->simulate(new SimulationInputDTO(
            audienceSize: 1000,
            averageOrderValue: 100,
            grossMarginPercentage: 50,
            historicalConversionRate: 5,
            campaignConversionRate: 20,
            fixedCampaignCost: 0,
            parameters: new PercentageDiscountParametersDTO(10),
        ));

        self::assertSame(5500.0, $result->netImpact);
        self::assertSame(6.25, $result->breakEvenConversionRate);
        self::assertSame(275.0, $result->roi);
        self::assertSame(HealthStatus::HEALTHY, $result->healthStatus);
    }

    public function test_it_marks_a_profitable_campaign_without_the_safety_margin_as_caution(): void
    {
        $result = (new PercentageDiscountStrategy())->simulate(new SimulationInputDTO(
            audienceSize: 1000,
            averageOrderValue: 100,
            grossMarginPercentage: 50,
            historicalConversionRate: 5,
            campaignConversionRate: 7,
            fixedCampaignCost: 0,
            parameters: new PercentageDiscountParametersDTO(10),
        ));

        self::assertSame(300.0, $result->netImpact);
        self::assertSame(HealthStatus::CAUTION, $result->healthStatus);
    }

    public function test_it_does_not_count_orders_when_campaign_conversion_does_not_exceed_baseline(): void
    {
        $result = (new PercentageDiscountStrategy())->simulate(new SimulationInputDTO(
            audienceSize: 1000,
            averageOrderValue: 100,
            grossMarginPercentage: 50,
            historicalConversionRate: 5,
            campaignConversionRate: 4,
            fixedCampaignCost: 0,
            parameters: new PercentageDiscountParametersDTO(10),
        ));

        self::assertSame(0.0, $result->incrementalOrders);
        self::assertSame(400.0, $result->discountCost);
        self::assertSame(-400.0, $result->netImpact);
        self::assertSame(-100.0, $result->roi);
        self::assertSame(HealthStatus::RISKY, $result->healthStatus);
    }

    public function test_it_returns_null_roi_when_the_campaign_has_no_cost(): void
    {
        $result = (new PercentageDiscountStrategy())->simulate(new SimulationInputDTO(
            audienceSize: 1000,
            averageOrderValue: 100,
            grossMarginPercentage: 50,
            historicalConversionRate: 5,
            campaignConversionRate: 10,
            fixedCampaignCost: 0,
            parameters: new PercentageDiscountParametersDTO(0),
        ));

        self::assertNull($result->roi);
        self::assertSame(2500.0, $result->incrementalContribution);
        self::assertSame(2500.0, $result->netImpact);
    }

    public function test_it_keeps_break_even_achievable_just_below_the_margin(): void
    {
        $result = (new PercentageDiscountStrategy())->simulate(new SimulationInputDTO(
            audienceSize: 1000,
            averageOrderValue: 100,
            grossMarginPercentage: 20,
            historicalConversionRate: 5,
            campaignConversionRate: 10,
            fixedCampaignCost: 0,
            parameters: new PercentageDiscountParametersDTO(19.99),
        ));

        self::assertTrue($result->breakEvenAchievable);
    }

    public function test_it_reports_when_break_even_requires_more_than_the_available_audience(): void
    {
        $result = (new PercentageDiscountStrategy())->simulate(new SimulationInputDTO(
            audienceSize: 100,
            averageOrderValue: 100,
            grossMarginPercentage: 50,
            historicalConversionRate: 5,
            campaignConversionRate: 10,
            fixedCampaignCost: 5000,
            parameters: new PercentageDiscountParametersDTO(10),
        ));

        self::assertTrue($result->breakEvenAchievable);
        self::assertSame(131.25, $result->breakEvenConversionRate);
    }
}
