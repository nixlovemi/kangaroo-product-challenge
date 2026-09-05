# 16 — Backend File Reference

This is the detailed map of the current backend application code.

## Domain/Campaigns

### DTOs

| Class | Responsibility |
|---|---|
| `CalculationStepDTO` | One explanatory calculation step |
| `CampaignDraftDTO` | Merchant campaign request/domain draft |
| `CampaignParameters` | Contract for campaign-specific parameter DTOs |
| `CampaignScenarioAnalysisDTO` | Merchant profile + scenario result collection |
| `DoublePointsParametersDTO` | Parameters for double-points campaigns |
| `MerchantOverviewDTO` | Merchant profile + expected conversion |
| `MerchantProfileDTO` | Historical merchant assumptions |
| `PercentageDiscountParametersDTO` | Parameters for percentage-discount campaigns |
| `ScenarioSimulationResultDTO` | Scenario type + conversion + simulation result |
| `SimulationInputDTO` | Fully resolved strategy input |
| `SimulationInsightDTO` | Merchant-facing explanations |
| `SimulationResultDTO` | Canonical simulation output |

### Enums

| Enum | Values / Purpose |
|---|---|
| `CampaignType` | `percentage_discount`, `double_points` |
| `HealthStatus` | `healthy`, `caution`, `risky` |
| `ScenarioType` | `conservative`, `expected`, `strong_response`, `custom` |

### Exceptions

- `InvalidCampaignParametersException`
- `MerchantProfileNotFoundException`
- `UnsupportedCampaignTypeException`

### Repository

- `HistoricalDataRepository` — abstraction for merchant historical data.

### Services

| Class | Responsibility |
|---|---|
| `CampaignSimulationService` | Loads merchant data, creates scenarios and delegates simulation |
| `CampaignSimulationStrategyFactory` | Resolves strategy by campaign type |
| `SimulationCalculationTrailBuilder` | Builds calculation explanation from result |
| `SimulationInsightCalculator` | Builds merchant-facing insight text |
| `CampaignSimulatorInterface` | Simulation contract exposed to recommendation memo |

### Strategies

| Class | Responsibility |
|---|---|
| `CampaignSimulationStrategy` | Strategy simulation contract |
| `AbstractCampaignSimulationStrategy` | Shared result/math assembly |
| `PercentageDiscountStrategy` | Percentage-discount economics |
| `DoublePointsStrategy` | Double-points economics |

## Domain/Recommendations

### DTOs

- `CampaignAdviceDTO` — overall scenario analysis + advice.
- `RecommendationDTO` — one recommendation.
- `RecommendationGoalDTO` — configurable recommendation thresholds.
- `RecommendationSetDTO` — recommendation collection + target state.
- `ScenarioAdviceDTO` — scenario result plus recommendations.

### Enums

- `RecommendationLever`
- `RecommendationOutcome`

### Levers

- `LeverAnalyzer` — analyzer contract.
- `AudienceLeverAnalyzer` — audience-size lever.
- `DiscountLeverAnalyzer` — discount lever.
- `FixedCostLeverAnalyzer` — fixed-cost lever.
- `PointsMultiplierLeverAnalyzer` — points multiplier lever.
- `FormatsRecommendationValues` — shared recommendation display helpers.

### Services

- `CampaignAdvisorService` — bridges Campaigns and Recommendations.
- `CampaignRecommendationEngine` — orchestrates lever analyzers.
- `CampaignRecommendationEngineInterface` — recommendation contract.
- `SimulationMemo` — request-scoped probe memoization.

## HTTP

### Controllers

- `CampaignSimulationController`
  - `simulate()`
  - `scenarios()`

- `MerchantProfileController`
  - `show()`

### Request

- `SimulationRequest`
  - validates simulation input;
  - maps campaign-type parameters to DTOs.

### Resources / Presenters

- `SimulationResultResource`
- `CampaignAdviceResource`
- `MerchantOverviewResource`
- `RecommendationSetPresenter`

### Middleware

Relevant challenge middleware:

- `ApiKeyMiddleware`
- Laravel standard middleware under `app/Http/Middleware/`

### Traits

- `ApiResponseTrait` — standardized success/error envelopes.

## Infrastructure

### Repositories

- `JsonHistoricalDataRepository`
- `CachedHistoricalDataRepository`

## Providers

### CampaignServiceProvider

Wires:

- insight calculator;
- calculation-trail builder;
- JSON repository;
- cached historical repository;
- strategy factory;
- simulator interface.

### RecommendationServiceProvider

Wires:

- recommendation goal from config;
- request-scoped `SimulationMemo`;
- recommendation engine and analyzers.

## Models

- `User` — standard Laravel user model; not central to the current campaign simulation workflow.

## Routes

`backend/routes/api.php`

```text
GET  /api/v1/merchants/{merchant}/profile
POST /api/v1/campaigns/simulate
POST /api/v1/campaigns/simulate/scenarios
```

## Configuration

Primary domain configuration:

`backend/config/campaigns.php`

API configuration:

`backend/config/api.php`

## Test Map

### Feature

- `CampaignServiceProviderTest`
- `CampaignSimulationApiTest`
- `MerchantProfileApiTest`

### Campaign unit tests

- `CampaignSimulationServiceTest`
- `SimulationCalculationTrailBuilderTest`
- `SimulationInsightCalculatorTest`
- `DoublePointsStrategyTest`
- `PercentageDiscountStrategyTest`

### Recommendation unit tests

- `CampaignRecommendationEngineTest`
- `SimulationMemoTest`
- `CostAndAudienceLeverAnalyzerTest`
- `DiscountLeverAnalyzerTest`

### Infrastructure unit tests

- `CachedHistoricalDataRepositoryTest`
- `JsonHistoricalDataRepositoryTest`

## Important Rule

This file is a **reference map**, not a substitute for the source code.

When a class gains a new responsibility or a new important dependency, update this file and the more specific domain document.
