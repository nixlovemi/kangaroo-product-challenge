<?php

return [
    'historical_data_cache_ttl_minutes' => (int) env('CAMPAIGN_HISTORICAL_DATA_CACHE_TTL', 5),
    'historical_data_version' => env('CAMPAIGN_HISTORICAL_DATA_VERSION', 'v1'),
    'historical_data_path' => env(
        'CAMPAIGN_HISTORICAL_DATA_PATH',
        'database/data/merchant_profiles.json',
    ),

    'recommendations' => [
        // A campaign at or above this ROI is considered good enough to launch.
        'target_roi_percentage' => (float) env('CAMPAIGN_RECOMMENDATION_TARGET_ROI', -5.0),

        // Floors below which a suggestion stops being a real offer a merchant would run.
        'minimum_viable_discount_percentage' => (float) env('CAMPAIGN_MINIMUM_VIABLE_DISCOUNT', 5.0),
        'minimum_viable_points_multiplier' => (float) env('CAMPAIGN_MINIMUM_VIABLE_MULTIPLIER', 1.25),

        'fixed_cost_probe_percentages' => [50.0, 75.0],
        'audience_probe_multiples' => [1.5, 2.0],

        // Mirrors the audience_size ceiling enforced by SimulationRequest.
        'maximum_audience_size' => 1000000,
    ],
];
