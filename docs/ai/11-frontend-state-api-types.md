# 11 — Frontend State, API Client and Types

## API Client

File:

`frontend/src/api/campaignSimulationClient.ts`

Class:

`CampaignSimulationClient`

Responsibilities:

- call backend endpoints;
- serialize request bodies;
- add API key header;
- parse standard API envelopes;
- convert failed responses into `CampaignSimulationApiError`;
- retry retryable network failures once.

## Retry Policy

`maxAttempts = 2`.

A retry occurs only for `TypeError`, which is treated as a retryable fetch/network failure.

API validation errors are not retried.

## API Error

`CampaignSimulationApiError` contains:

- message;
- field-level errors.

This lets the UI distinguish general failures from validation information if needed.

## Composable

File:

`frontend/src/composables/useCampaignAdvisor.ts`

The composable is the frontend orchestration layer.

### State

```text
step
merchantId
audienceSize
fixedCampaignCost
campaignType
discountPercentage
pointsMultiplier
customConversionRate
merchantOverview
analysis
loading
error
```

### Derived State

- `selectedScenario`
- `scenarios`
- `currentScenario`
- `merchant`
- `assumptions`

### Main Actions

- `loadMerchantOverview`
- `selectMerchant`
- `goToStep`
- `analyze`
- `selectScenario`
- `setCustomConversionRate`
- `applyCustomScenario`
- `reset`

## Merchant Loading

When merchant selection changes:

1. merchant ID changes;
2. previous merchant overview is cleared;
3. merchant overview is fetched;
4. the new expected conversion becomes available.

Clearing the old overview intentionally prevents displaying stale default assumptions while the new merchant is loading.

## Analyze

`analyze()` creates the API request:

```text
merchant_id
audience_size
fixed_campaign_cost
campaign_type
campaign_conversion_rate
parameters
```

Parameter shape depends on campaign type.

On success:

- analysis is stored;
- wizard moves to analysis step;
- selected scenario is synchronized with custom/expected state.

On failure:

- previous analysis is cleared;
- error message is stored;
- loading ends.

## TypeScript API Contract

`src/types/campaign.ts` mirrors the backend response contract.

Important types:

- `CampaignType`
- `ScenarioType`
- `HealthStatus`
- `SimulationRequestBody`
- `MerchantOverviewData`
- `ScenarioAnalysisData`
- `SimulationMetrics`
- `ScenarioAnalysis`
- `Recommendation`
- `RecommendationSet`

When the backend contract changes, update these types and their tests/consumers.

## Frontend/Backend Boundary Rule

The frontend should not infer missing financial fields by recalculating them.

If the UI needs a new financial value, prefer extending the backend response contract rather than duplicating domain logic in TypeScript.
