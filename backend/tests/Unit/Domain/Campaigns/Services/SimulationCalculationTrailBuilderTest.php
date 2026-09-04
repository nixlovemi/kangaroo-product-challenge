<?php

namespace Tests\Unit\Domain\Campaigns\Services;

use App\Domain\Campaigns\DTOs\SimulationInsightDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;
use App\Domain\Campaigns\Enums\HealthStatus;
use App\Domain\Campaigns\Services\SimulationCalculationTrailBuilder;
use PHPUnit\Framework\TestCase;

final class SimulationCalculationTrailBuilderTest extends TestCase
{
    public function test_it_explains_every_number_that_leads_to_the_net_impact(): void
    {
        $steps = (new SimulationCalculationTrailBuilder())->build($this->simulationResult());

        $labels = array_map(fn ($step) => $step->label, $steps);

        self::assertSame([
            'Baseline orders',
            'Campaign orders',
            'Incremental orders',
            'Incremental revenue',
            'Incremental contribution',
            'Incentive cost',
            'Fixed campaign cost',
            'Net impact',
            'Break-even conversion',
            'Return on investment',
        ], $labels);
    }

    public function test_it_shows_the_operands_behind_the_incremental_orders(): void
    {
        $steps = (new SimulationCalculationTrailBuilder())->build($this->simulationResult());

        $step = $this->stepByLabel($steps, 'Incremental orders');

        self::assertSame('48.00 campaign orders − 36.00 baseline orders', $step->formula);
        self::assertSame(12.0, $step->value);
        self::assertSame('count', $step->valueType);
    }

    public function test_it_shows_the_operands_behind_the_net_impact(): void
    {
        $steps = (new SimulationCalculationTrailBuilder())->build($this->simulationResult());

        $step = $this->stepByLabel($steps, 'Net impact');

        self::assertSame('456.96 contribution − 537.60 incentive cost − 250.00 fixed cost', $step->formula);
        self::assertSame(-330.64, $step->value);
        self::assertSame('currency', $step->valueType);
    }

    public function test_it_omits_the_roi_step_when_there_is_no_campaign_cost(): void
    {
        $steps = (new SimulationCalculationTrailBuilder())->build($this->simulationResult(roi: null));

        $labels = array_map(fn ($step) => $step->label, $steps);

        self::assertNotContains('Return on investment', $labels);
    }

    public function test_it_spells_out_that_profit_per_order_is_money_not_the_margin_percentage(): void
    {
        $steps = (new SimulationCalculationTrailBuilder())->build($this->simulationResult());

        $step = $this->stepByLabel($steps, 'Incremental contribution');

        // 112.00 order value × 34% margin = 38.08 profit per order; without the
        // percentage spelled out, readers mistake 38.08 for the margin itself.
        self::assertSame(
            '12.00 incremental orders × 38.08 profit per order (34.00% gross margin on a 112.00 order)',
            $step->formula,
        );
    }

    private function simulationResult(?float $roi = -41.98): SimulationResultDTO
    {
        return new SimulationResultDTO(
            baselineOrders: 36,
            campaignOrders: 48,
            incrementalOrders: 12,
            incrementalRevenue: 1344,
            incentiveCost: 537.6,
            incrementalContribution: 456.96,
            netImpact: -330.64,
            breakEvenConversionRate: 6.35,
            roi: $roi,
            healthStatus: HealthStatus::RISKY,
            breakEvenAchievable: true,
            insight: new SimulationInsightDTO(0, 0, 'driver', 'action', 'orders'),
            fixedCampaignCost: 250,
            averageOrderValue: 112,
        );
    }

    /**
     * @param \App\Domain\Campaigns\DTOs\CalculationStepDTO[] $steps
     */
    private function stepByLabel(array $steps, string $label): \App\Domain\Campaigns\DTOs\CalculationStepDTO
    {
        foreach ($steps as $step) {
            if ($step->label === $label) {
                return $step;
            }
        }

        self::fail("Calculation step \"{$label}\" was not found.");
    }
}
