<?php

namespace Tests\Unit\Domain\Recommendations\Levers;

use App\Domain\Campaigns\DTOs\MerchantProfileDTO;
use App\Domain\Campaigns\Enums\CampaignType;
use App\Domain\Campaigns\Repositories\HistoricalDataRepository;
use App\Domain\Campaigns\Services\CampaignSimulationService;
use App\Domain\Campaigns\Services\CampaignSimulationStrategyFactory;
use App\Domain\Campaigns\Strategies\PercentageDiscountStrategy;
use App\Domain\Recommendations\Enums\RecommendationOutcome;
use App\Domain\Recommendations\Levers\AudienceLeverAnalyzer;
use App\Domain\Recommendations\Levers\FixedCostLeverAnalyzer;
use App\Domain\Recommendations\Services\SimulationMemo;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Domain\Recommendations\BuildsRecommendationFixtures;

final class CostAndAudienceLeverAnalyzerTest extends TestCase
{
    use BuildsRecommendationFixtures;

    public function test_fixed_cost_lever_reports_that_no_cut_can_rescue_an_unprofitable_incentive(): void
    {
        $recommendation = (new FixedCostLeverAnalyzer())->analyze(
            $this->input(),
            $this->simulationResult(),
            $this->goal(),
            $this->memo(),
        );

        self::assertSame(RecommendationOutcome::INFEASIBLE, $recommendation->outcome);
        self::assertNull($recommendation->suggestedValue);
        self::assertStringContainsString('even at zero', $recommendation->message);
    }

    public function test_fixed_cost_lever_is_skipped_when_there_is_no_fixed_cost(): void
    {
        self::assertFalse((new FixedCostLeverAnalyzer())->supports($this->input(fixedCampaignCost: 0)));
    }

    public function test_audience_lever_warns_against_scaling_a_loss_making_campaign(): void
    {
        // Incentive cost exceeds contribution, so every extra customer deepens the loss.
        $recommendation = (new AudienceLeverAnalyzer())->analyze(
            $this->input(),
            $this->simulationResult(incrementalContribution: 255.90, incentiveCost: 491.90),
            $this->goal(),
            $this->memo(),
        );

        self::assertSame(RecommendationOutcome::INFEASIBLE, $recommendation->outcome);
        self::assertStringContainsString('multiplies the loss', $recommendation->message);
    }

    public function test_audience_lever_suggests_scaling_when_each_extra_customer_is_profitable(): void
    {
        // High margin with a heavy flat cost: each extra customer earns more than the
        // incentive costs, so a wider audience genuinely dilutes the fixed cost.
        $input = new \App\Domain\Campaigns\DTOs\SimulationInputDTO(
            audienceSize: 1000,
            averageOrderValue: 68.50,
            grossMarginPercentage: 58,
            historicalConversionRate: 4.8,
            campaignConversionRate: 6.82,
            fixedCampaignCost: 900,
            parameters: new \App\Domain\Campaigns\DTOs\PercentageDiscountParametersDTO(5),
        );

        $result = $this->memo()->simulate($input);

        $recommendation = (new AudienceLeverAnalyzer())->analyze($input, $result, $this->goal(), $this->memo());

        self::assertSame(RecommendationOutcome::ACTIONABLE, $recommendation->outcome);
        self::assertSame(1500.0, (float) $recommendation->suggestedValue);
    }

    private function memo(): SimulationMemo
    {
        $factory = new CampaignSimulationStrategyFactory([
            CampaignType::PERCENTAGE_DISCOUNT->value => new PercentageDiscountStrategy(),
        ]);

        $repository = new class implements HistoricalDataRepository {
            public function getMerchantProfile(int $merchantId): MerchantProfileDTO
            {
                return new MerchantProfileDTO(
                    merchantId: $merchantId,
                    merchantName: 'Test Merchant',
                    currency: 'CAD',
                    averageOrderValue: 112,
                    grossMarginPercentage: 34,
                    historicalConversionRate: 3.1,
                    historicalCampaignLiftPercentage: 18,
                    pointsCostPerUnit: 0.01,
                    pointsRedemptionPercentage: 40,
                );
            }
        };

        return new SimulationMemo(new CampaignSimulationService($factory, $repository));
    }
}
