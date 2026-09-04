<?php

namespace App\Providers;

use App\Domain\Recommendations\DTOs\RecommendationGoalDTO;
use App\Domain\Recommendations\Levers\AudienceLeverAnalyzer;
use App\Domain\Recommendations\Levers\DiscountLeverAnalyzer;
use App\Domain\Recommendations\Levers\FixedCostLeverAnalyzer;
use App\Domain\Recommendations\Levers\PointsMultiplierLeverAnalyzer;
use App\Domain\Recommendations\Services\CampaignRecommendationEngine;
use App\Domain\Recommendations\Services\CampaignRecommendationEngineInterface;
use App\Domain\Recommendations\Services\SimulationMemo;
use Illuminate\Support\ServiceProvider;

final class RecommendationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RecommendationGoalDTO::class, function (): RecommendationGoalDTO {
            $settings = config('campaigns.recommendations');

            return new RecommendationGoalDTO(
                targetRoiPercentage: (float) $settings['target_roi_percentage'],
                minimumViableDiscountPercentage: (float) $settings['minimum_viable_discount_percentage'],
                minimumViablePointsMultiplier: (float) $settings['minimum_viable_points_multiplier'],
                fixedCostProbePercentages: $settings['fixed_cost_probe_percentages'],
                audienceProbeMultiples: $settings['audience_probe_multiples'],
                maximumAudienceSize: (int) $settings['maximum_audience_size'],
            );
        });

        // Scoped, not singleton: the memo must not leak probe results between requests.
        $this->app->scoped(SimulationMemo::class);

        $this->app->bind(CampaignRecommendationEngineInterface::class, function ($app): CampaignRecommendationEngine {
            return new CampaignRecommendationEngine(
                [
                    $app->make(DiscountLeverAnalyzer::class),
                    $app->make(PointsMultiplierLeverAnalyzer::class),
                    $app->make(FixedCostLeverAnalyzer::class),
                    $app->make(AudienceLeverAnalyzer::class),
                ],
                $app->make(SimulationMemo::class),
            );
        });
    }
}
