<?php

namespace Tests\Unit\Domain\Recommendations\Levers;

use App\Domain\Campaigns\DTOs\PercentageDiscountParametersDTO;
use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\Services\CampaignSimulationService;
use App\Domain\Campaigns\Services\CampaignSimulationStrategyFactory;
use App\Domain\Campaigns\Enums\CampaignType;
use App\Domain\Campaigns\Repositories\HistoricalDataRepository;
use App\Domain\Campaigns\DTOs\MerchantProfileDTO;
use App\Domain\Campaigns\Strategies\PercentageDiscountStrategy;
use App\Domain\Recommendations\Enums\RecommendationOutcome;
use App\Domain\Recommendations\Levers\DiscountLeverAnalyzer;
use App\Domain\Recommendations\Services\SimulationMemo;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Domain\Recommendations\BuildsRecommendationFixtures;

final class DiscountLeverAnalyzerTest extends TestCase
{
    use BuildsRecommendationFixtures;

    public function test_it_refuses_to_suggest_a_discount_too_small_to_be_a_real_offer(): void
    {
        // Merchant 202's real numbers: reaching -5% ROI would need a 0.39% discount, which
        // is not a campaign anyone would run.
        $recommendation = (new DiscountLeverAnalyzer())->analyze(
            $this->input(),
            $this->simulationResult(),
            $this->goal(),
            $this->memo(),
        );

        self::assertSame(RecommendationOutcome::IMPLAUSIBLE, $recommendation->outcome);
        self::assertNull($recommendation->suggestedValue);
        self::assertStringContainsString('0.39%', $recommendation->message);
    }

    public function test_it_suggests_a_lower_discount_when_the_required_cut_is_still_a_real_offer(): void
    {
        // Healthier contribution, so the required discount lands above the plausibility floor.
        $recommendation = (new DiscountLeverAnalyzer())->analyze(
            $this->input(parameters: new PercentageDiscountParametersDTO(15), fixedCampaignCost: 100),
            $this->simulationResult(incrementalContribution: 562, incentiveCost: 738, fixedCampaignCost: 100, roi: -34.5),
            $this->goal(),
            $this->memo(),
        );

        self::assertSame(RecommendationOutcome::ACTIONABLE, $recommendation->outcome);
        self::assertNotNull($recommendation->suggestedValue);
        self::assertLessThan(15.0, $recommendation->suggestedValue);
    }

    public function test_it_rounds_the_suggested_discount_down_to_a_clean_half_point(): void
    {
        $recommendation = (new DiscountLeverAnalyzer())->analyze(
            $this->input(parameters: new PercentageDiscountParametersDTO(15), fixedCampaignCost: 100),
            $this->simulationResult(incrementalContribution: 562, incentiveCost: 738, fixedCampaignCost: 100, roi: -34.5),
            $this->goal(),
            $this->memo(),
        );

        self::assertSame(9.5, $recommendation->suggestedValue);
    }

    public function test_it_ignores_campaigns_that_have_no_discount_to_cut(): void
    {
        $recommendation = (new DiscountLeverAnalyzer())->analyze(
            $this->input(parameters: new PercentageDiscountParametersDTO(0)),
            $this->simulationResult(),
            $this->goal(),
            $this->memo(),
        );

        self::assertNull($recommendation);
    }

    public function test_it_only_supports_percentage_discount_campaigns(): void
    {
        $analyzer = new DiscountLeverAnalyzer();

        self::assertTrue($analyzer->supports($this->input()));
        self::assertFalse($analyzer->supports($this->input(
            parameters: new \App\Domain\Campaigns\DTOs\DoublePointsParametersDTO(2),
            campaignType: CampaignType::DOUBLE_POINTS,
        )));
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

