<?php

namespace Tests\Unit\Domain\Campaigns\Strategies;

use App\Domain\Campaigns\DTOs\DoublePointsParametersDTO;
use App\Domain\Campaigns\DTOs\PercentageDiscountParametersDTO;
use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\Enums\CampaignType;
use App\Domain\Campaigns\Enums\HealthStatus;
use App\Domain\Campaigns\Exceptions\InvalidCampaignParametersException;
use App\Domain\Campaigns\Strategies\DoublePointsStrategy;
use PHPUnit\Framework\TestCase;

final class DoublePointsStrategyTest extends TestCase
{
    public function test_it_calculates_the_incremental_points_cost(): void
    {
        $result = (new DoublePointsStrategy())->simulate(new SimulationInputDTO(
            audienceSize: 1000,
            averageOrderValue: 100,
            grossMarginPercentage: 50,
            historicalConversionRate: 5,
            campaignConversionRate: 10,
            fixedCampaignCost: 0,
            parameters: new DoublePointsParametersDTO(pointsMultiplier: 2),
            campaignType: CampaignType::DOUBLE_POINTS,
            pointsEarnedPerCurrency: 10,
            pointsCostPerUnit: 0.01,
            pointsRedemptionPercentage: 40,
        ));

        self::assertSame(400.0, $result->incentiveCost);
        self::assertSame(50.0, $result->incrementalOrders);
        self::assertSame(2100.0, $result->netImpact);
        self::assertSame(5.43, $result->breakEvenConversionRate);
        self::assertSame(525.0, $result->roi);
        self::assertSame(HealthStatus::HEALTHY, $result->healthStatus);
    }

    public function test_it_does_not_add_incentive_cost_when_the_multiplier_is_one(): void
    {
        $result = (new DoublePointsStrategy())->simulate(new SimulationInputDTO(
            audienceSize: 1000,
            averageOrderValue: 100,
            grossMarginPercentage: 50,
            historicalConversionRate: 5,
            campaignConversionRate: 10,
            fixedCampaignCost: 0,
            parameters: new DoublePointsParametersDTO(pointsMultiplier: 1),
            campaignType: CampaignType::DOUBLE_POINTS,
        ));

        self::assertSame(0.0, $result->incentiveCost);
        self::assertNull($result->roi);
    }

    public function test_it_rejects_percentage_discount_parameters(): void
    {
        $this->expectException(InvalidCampaignParametersException::class);

        (new DoublePointsStrategy())->simulate(new SimulationInputDTO(
            audienceSize: 1000,
            averageOrderValue: 100,
            grossMarginPercentage: 50,
            historicalConversionRate: 5,
            campaignConversionRate: 10,
            fixedCampaignCost: 0,
            parameters: new PercentageDiscountParametersDTO(10),
            campaignType: CampaignType::DOUBLE_POINTS,
        ));
    }

    public function test_it_has_no_expected_points_cost_when_redemption_is_zero(): void
    {
        $result = (new DoublePointsStrategy())->simulate(new SimulationInputDTO(
            audienceSize: 1000,
            averageOrderValue: 100,
            grossMarginPercentage: 50,
            historicalConversionRate: 5,
            campaignConversionRate: 10,
            fixedCampaignCost: 0,
            parameters: new DoublePointsParametersDTO(),
            campaignType: CampaignType::DOUBLE_POINTS,
            pointsRedemptionPercentage: 0,
        ));

        self::assertSame(0.0, $result->incentiveCost);
        self::assertSame(2500.0, $result->netImpact);
    }

    public function test_it_uses_the_full_points_cost_when_redemption_is_one_hundred_percent(): void
    {
        $result = (new DoublePointsStrategy())->simulate(new SimulationInputDTO(
            audienceSize: 1000,
            averageOrderValue: 100,
            grossMarginPercentage: 50,
            historicalConversionRate: 5,
            campaignConversionRate: 10,
            fixedCampaignCost: 0,
            parameters: new DoublePointsParametersDTO(),
            campaignType: CampaignType::DOUBLE_POINTS,
            pointsEarnedPerCurrency: 10,
            pointsRedemptionPercentage: 100,
        ));

        self::assertSame(1000.0, $result->incentiveCost);
        self::assertSame(1500.0, $result->netImpact);
    }

    public function test_it_marks_break_even_as_unachievable_when_points_cost_exceeds_contribution(): void
    {
        $result = (new DoublePointsStrategy())->simulate(new SimulationInputDTO(
            audienceSize: 1000,
            averageOrderValue: 100,
            grossMarginPercentage: 5,
            historicalConversionRate: 5,
            campaignConversionRate: 10,
            fixedCampaignCost: 0,
            parameters: new DoublePointsParametersDTO(),
            campaignType: CampaignType::DOUBLE_POINTS,
            pointsEarnedPerCurrency: 10,
            pointsRedemptionPercentage: 100,
        ));

        self::assertFalse($result->breakEvenAchievable);
        self::assertSame(100.0, $result->breakEvenConversionRate);
        self::assertSame(HealthStatus::RISKY, $result->healthStatus);
    }

    public function test_it_rejects_a_multiplier_below_one(): void
    {
        $this->expectException(InvalidCampaignParametersException::class);

        (new DoublePointsStrategy())->simulate(new SimulationInputDTO(
            audienceSize: 1000,
            averageOrderValue: 100,
            grossMarginPercentage: 50,
            historicalConversionRate: 5,
            campaignConversionRate: 10,
            fixedCampaignCost: 0,
            parameters: new DoublePointsParametersDTO(pointsMultiplier: 0.5),
            campaignType: CampaignType::DOUBLE_POINTS,
        ));
    }
}
