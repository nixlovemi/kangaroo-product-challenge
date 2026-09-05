# 06 — Data and Infrastructure

## HistoricalDataRepository

Domain interface:

`backend/app/Domain/Campaigns/Repositories/HistoricalDataRepository.php`

Contract:

```text
getMerchantProfile(int $merchantId): MerchantProfileDTO
```

The Campaigns domain depends on this abstraction rather than on JSON or Laravel filesystem details.

## JSON Implementation

`JsonHistoricalDataRepository` reads:

`backend/database/data/merchant_profiles.json`

It:

1. loads the JSON file;
2. decodes it with `JSON_THROW_ON_ERROR`;
3. looks up the merchant by string ID;
4. maps the record to `MerchantProfileDTO`;
5. throws `MerchantProfileNotFoundException` when absent;
6. wraps file/JSON errors in a runtime failure.

## Cached Implementation

`CachedHistoricalDataRepository` decorates another repository.

```text
HistoricalDataRepository
        ^
        |
CachedHistoricalDataRepository
        |
JsonHistoricalDataRepository
```

The cache key includes:

- data version;
- merchant ID.

This allows changing the configured data version to avoid stale profile data.

## Configuration

`config/campaigns.php` controls:

- historical data path;
- cache TTL;
- historical data version;
- recommendation target;
- minimum viable recommendation thresholds;
- probe percentages/multiples;
- maximum audience size.

## Current Merchant Fixtures

The challenge contains two merchant profiles:

### Merchant 101

Atelier Nord Cafe

- CAD
- AOV: 68.50
- gross margin: 58%
- historical conversion: 4.8%
- historical lift: 42%
- points cost: 0.01
- redemption: 38%
- points earned per currency: 10

### Merchant 202

Saint-Paul Market

- CAD
- AOV: 112
- gross margin: 34%
- historical conversion: 3.1%
- historical lift: 18%
- points cost: 0.012
- redemption: 54%
- points earned per currency: 12

## Infrastructure Replacement

A future database-backed implementation should implement `HistoricalDataRepository`.

The Campaigns domain and recommendation domain should not need to know that the source changed.

## Security Note

The frontend currently uses an API key from environment configuration for this challenge/demo setup.

Never commit real credentials or production secrets into the repository.
