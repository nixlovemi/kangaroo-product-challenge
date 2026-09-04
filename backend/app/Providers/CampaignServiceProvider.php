<?php

namespace App\Providers;

use App\Domain\Campaigns\Enums\CampaignType;
use App\Domain\Campaigns\Services\CampaignSimulationService;
use App\Domain\Campaigns\Services\CampaignSimulationStrategyFactory;
use App\Domain\Campaigns\Strategies\PercentageDiscountStrategy;
use App\Domain\Campaigns\Strategies\DoublePointsStrategy;
use App\Domain\Campaigns\Repositories\HistoricalDataRepository;
use App\Domain\Campaigns\Services\SimulationCalculationTrailBuilder;
use App\Domain\Campaigns\Services\SimulationCalculationTrailBuilderInterface;
use App\Domain\Campaigns\Services\SimulationInsightCalculator;
use App\Domain\Campaigns\Services\SimulationInsightCalculatorInterface;
use App\Infrastructure\Campaigns\Repositories\JsonHistoricalDataRepository;
use App\Infrastructure\Campaigns\Repositories\CachedHistoricalDataRepository;
use Illuminate\Support\ServiceProvider;

final class CampaignServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SimulationInsightCalculatorInterface::class, SimulationInsightCalculator::class);
        $this->app->bind(SimulationCalculationTrailBuilderInterface::class, SimulationCalculationTrailBuilder::class);

        $this->app->singleton(JsonHistoricalDataRepository::class, function ($app) {
            return new JsonHistoricalDataRepository(
                $app->make(\Illuminate\Filesystem\Filesystem::class),
                base_path(config('campaigns.historical_data_path')),
            );
        });

        // The provider selects the infrastructure source; replacing JSON with a database implementation keeps the domain and cache unchanged.
        $this->app->singleton(HistoricalDataRepository::class, function ($app) {
            return new CachedHistoricalDataRepository(
                $app->make(JsonHistoricalDataRepository::class),
                $app->make(\Illuminate\Contracts\Cache\Repository::class),
                config('campaigns.historical_data_cache_ttl_minutes'),
                config('campaigns.historical_data_version'),
            );
        });

        $this->app->singleton(CampaignSimulationStrategyFactory::class, function ($app) {
            return new CampaignSimulationStrategyFactory([
                CampaignType::PERCENTAGE_DISCOUNT->value => $app->make(PercentageDiscountStrategy::class),
                CampaignType::DOUBLE_POINTS->value => $app->make(DoublePointsStrategy::class),
            ]);
        });

        $this->app->singleton(CampaignSimulationService::class);
    }
}
