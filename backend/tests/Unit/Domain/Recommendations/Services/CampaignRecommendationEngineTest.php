<?php

namespace Tests\Unit\Domain\Recommendations\Services;

use App\Domain\Campaigns\DTOs\MerchantProfileDTO;
use App\Domain\Campaigns\DTOs\PercentageDiscountParametersDTO;
use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\Enums\CampaignType;
use App\Domain\Campaigns\Repositories\HistoricalDataRepository;
use App\Domain\Campaigns\Services\CampaignSimulationService;
use App\Domain\Campaigns\Services\CampaignSimulationStrategyFactory;
use App\Domain\Campaigns\Strategies\PercentageDiscountStrategy;
use App\Domain\Recommendations\Levers\AudienceLeverAnalyzer;
use App\Domain\Recommendations\Levers\DiscountLeverAnalyzer;
use App\Domain\Recommendations\Levers\FixedCostLeverAnalyzer;
use App\Domain\Recommendations\Services\CampaignRecommendationEngine;
use App\Domain\Recommendations\Services\SimulationMemo;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Domain\Recommendations\BuildsRecommendationFixtures;

final class CampaignRecommendationEngineTest extends TestCase
{
    use BuildsRecommendationFixtures;

    public function test_it_skips_every_lever_when_the_campaign_already_meets_the_target(): void
    {
        $set = $this->engine()->recommend(
            $this->input(),
            $this->simulationResult(roi: 12.0),
            $this->goal(),
        );

        self::assertTrue($set->alreadyMeetsTarget);
        self::assertSame([], $set->recommendations);
        self::assertStringContainsString('already clears', $set->summaryMessage);
    }

    public function test_it_explains_that_no_single_change_works_when_the_incentive_is_the_problem(): void
    {
        $set = $this->engine()->recommend($this->input(), $this->simulationResult(), $this->goal());

        self::assertFalse($set->alreadyMeetsTarget);
        self::assertNotSame([], $set->recommendations);
        self::assertStringContainsString('needs a different offer rather than a tweak', $set->summaryMessage);

        foreach ($set->recommendations as $recommendation) {
            self::assertFalse($recommendation->outcome->isActionable());
        }
    }

    public function test_it_lists_actionable_recommendations_before_diagnoses(): void
    {
        $input = new SimulationInputDTO(
            audienceSize: 1000,
            averageOrderValue: 68.50,
            grossMarginPercentage: 58,
            historicalConversionRate: 4.8,
            campaignConversionRate: 6.82,
            fixedCampaignCost: 900,
            parameters: new PercentageDiscountParametersDTO(5),
        );

        $set = $this->engine()->recommend($input, $this->memo()->simulate($input), $this->goal());

        $outcomes = array_map(fn ($item): bool => $item->outcome->isActionable(), $set->recommendations);
        $sorted = $outcomes;
        rsort($sorted);

        self::assertSame($sorted, $outcomes);
        self::assertTrue($outcomes[0]);
    }

    private function engine(): CampaignRecommendationEngine
    {
        return new CampaignRecommendationEngine(
            [
                new DiscountLeverAnalyzer(),
                new FixedCostLeverAnalyzer(),
                new AudienceLeverAnalyzer(),
            ],
            $this->memo(),
        );
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
