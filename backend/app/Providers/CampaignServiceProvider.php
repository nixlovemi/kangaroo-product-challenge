<?php

namespace App\Providers;

use App\Domain\Campaigns\Enums\CampaignType;
use App\Domain\Campaigns\Services\CampaignSimulationService;
use App\Domain\Campaigns\Services\CampaignSimulationStrategyFactory;
use App\Domain\Campaigns\Strategies\PercentageDiscountStrategy;
use App\Domain\Campaigns\Strategies\DoublePointsStrategy;
use App\Domain\Campaigns\Repositories\HistoricalDataRepository;
use App\Infrastructure\Campaigns\Repositories\JsonHistoricalDataRepository;
use Illuminate\Support\ServiceProvider;

final class CampaignServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HistoricalDataRepository::class, function ($app) {
            return new JsonHistoricalDataRepository(
                $app->make(\Illuminate\Filesystem\Filesystem::class),
                base_path(config('campaigns.historical_data_path')),
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
