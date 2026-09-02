<?php

return [
    'historical_data_cache_ttl_minutes' => (int) env('CAMPAIGN_HISTORICAL_DATA_CACHE_TTL', 5),
    'historical_data_version' => env('CAMPAIGN_HISTORICAL_DATA_VERSION', 'v1'),
    'historical_data_path' => env(
        'CAMPAIGN_HISTORICAL_DATA_PATH',
        'database/data/merchant_profiles.json',
    ),
];
