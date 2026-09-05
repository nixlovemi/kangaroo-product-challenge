# 03 — Backend Architecture

## Layers

### HTTP layer

Located primarily under:

`backend/app/Http/`

Responsibilities:

- receive requests;
- validate input;
- map transport data to domain DTOs;
- invoke domain/application services;
- map domain results to API resources;
- return standardized API responses.

Controllers should remain thin.

### Domain layer

Located under:

`backend/app/Domain/`

Contains the business concepts and behavior.

Two contexts exist:

- `Domain/Campaigns`
- `Domain/Recommendations`

### Infrastructure layer

Located under:

`backend/app/Infrastructure/`

Contains concrete data-access implementations.

## Request Lifecycle

For scenario analysis:

```text
POST /api/v1/campaigns/simulate/scenarios
        ↓
ApiKeyMiddleware / throttle
        ↓
CampaignSimulationController::scenarios()
        ↓
SimulationRequest validation
        ↓
SimulationRequest::toCampaignDraft()
        ↓
CampaignAdvisorService::analyzeScenarios()
        ↓
CampaignSimulationService::simulateScenariosForMerchant()
        ↓
HistoricalDataRepository
        ↓
scenario generation
        ↓
CampaignSimulationStrategyFactory
        ↓
PercentageDiscountStrategy / DoublePointsStrategy
        ↓
SimulationResultDTO
        ↓
CampaignRecommendationEngine
        ↓
Lever analyzers
        ↓
CampaignAdviceResource
        ↓
JSON API response
```

## Form Request

`SimulationRequest` owns transport validation.

It also explicitly maps campaign-type-specific parameters into:

- `PercentageDiscountParametersDTO`
- `DoublePointsParametersDTO`

The current code comments acknowledge that this explicit mapping can move to a factory/provider if campaign types grow significantly.

## DTOs

DTOs are immutable typed structures used to move domain data between layers.

Important examples:

- `CampaignDraftDTO`
- `SimulationInputDTO`
- `SimulationResultDTO`
- `CampaignScenarioAnalysisDTO`
- `ScenarioSimulationResultDTO`

They reduce loose associative-array coupling inside the domain.

## Strategies

`CampaignSimulationStrategy` defines the simulation contract.

Current implementations:

- `PercentageDiscountStrategy`
- `DoublePointsStrategy`

`AbstractCampaignSimulationStrategy` contains shared calculations and result assembly.

The strategy-specific classes calculate incentive economics and break-even behavior, while the abstract base handles shared result derivation.

## Factory

`CampaignSimulationStrategyFactory` selects a strategy based on `CampaignType`.

The mapping is configured in `CampaignServiceProvider`.

## Insights

`SimulationInsightCalculator` converts numerical simulation state into merchant-facing explanatory messages.

This keeps explanatory text out of campaign-specific strategies.

## Calculation Trail

`SimulationCalculationTrailBuilder` creates a line-by-line explanation from `SimulationResultDTO`.

The builder intentionally reads the result DTO instead of strategy internals, allowing different strategies to share the same explanation mechanism.

## API Resources

Resources/presenters translate domain DTOs into the public API contract.

Examples:

- `SimulationResultResource`
- `CampaignAdviceResource`
- `MerchantOverviewResource`
- `RecommendationSetPresenter`

The domain should not be responsible for JSON serialization details.

## Exceptions

Domain-specific exceptions represent meaningful domain failures:

- invalid campaign parameters;
- unsupported campaign type;
- missing merchant profile.

HTTP handling translates these failures into appropriate API responses.
