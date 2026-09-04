<?php

namespace Tests\Unit\Domain\Campaigns\Services;

use App\Domain\Campaigns\Enums\HealthStatus;
use App\Domain\Campaigns\Services\SimulationInsightCalculator;
use PHPUnit\Framework\TestCase;

final class SimulationInsightCalculatorTest extends TestCase
{
    public function test_caution_action_message_does_not_claim_the_campaign_is_safely_past_break_even(): void
    {
        // Conversion sits right at break-even (rate gap <= 0), which used to make
        // actionMessage() fall into the "no changes needed" branch even though
        // CAUTION means there is little safety margin.
        $insight = (new SimulationInsightCalculator())->build(
            audienceSize: 1200,
            baselineOrders: 48,
            campaignOrders: 76,
            incrementalOrders: 28,
            campaignConversionRate: 6.35,
            breakEvenConversionRate: 6.35,
            healthStatus: HealthStatus::CAUTION,
        );

        self::assertStringNotContainsString('no changes needed', $insight->actionMessage);
        self::assertStringContainsString('little safety margin', $insight->actionMessage);
    }

    public function test_healthy_action_message_reports_no_changes_needed(): void
    {
        $insight = (new SimulationInsightCalculator())->build(
            audienceSize: 1200,
            baselineOrders: 48,
            campaignOrders: 90,
            incrementalOrders: 42,
            campaignConversionRate: 8,
            breakEvenConversionRate: 6.35,
            healthStatus: HealthStatus::HEALTHY,
        );

        self::assertSame(
            'Already past break-even with room to spare — no changes needed to stay profitable.',
            $insight->actionMessage,
        );
    }

    public function test_risky_action_message_reports_the_numeric_gap_to_close(): void
    {
        $insight = (new SimulationInsightCalculator())->build(
            audienceSize: 1200,
            baselineOrders: 48,
            campaignOrders: 60,
            incrementalOrders: 12,
            campaignConversionRate: 5,
            breakEvenConversionRate: 6.35,
            healthStatus: HealthStatus::RISKY,
        );

        self::assertSame(
            'Needs 16 more incremental orders (+1.35pp conversion) to break even.',
            $insight->actionMessage,
        );
    }

    public function test_break_even_incremental_orders_is_rounded_only_once_at_the_dto_boundary(): void
    {
        $insight = (new SimulationInsightCalculator())->build(
            audienceSize: 1200,
            baselineOrders: 50.4,
            campaignOrders: 78,
            incrementalOrders: 27.6,
            campaignConversionRate: 6.5,
            breakEvenConversionRate: 7.23,
            healthStatus: HealthStatus::RISKY,
        );

        self::assertSame(36.36, $insight->breakEvenIncrementalOrders);
    }
}
