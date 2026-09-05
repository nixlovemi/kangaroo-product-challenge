# 14 — Design Principles and Engineering Practices

## SOLID

The project uses SOLID principles as practical design constraints rather than as an excuse to create abstractions everywhere.

## Single Responsibility Principle

Examples:

- Controllers handle HTTP orchestration.
- Form Requests handle input validation/mapping.
- `CampaignSimulationService` orchestrates merchant simulation.
- Strategies handle campaign-specific incentive/break-even behavior.
- `SimulationInsightCalculator` handles merchant-facing simulation explanations.
- `SimulationCalculationTrailBuilder` builds calculation explanations.
- `CampaignRecommendationEngine` orchestrates recommendation levers.
- Each `LeverAnalyzer` handles one recommendation lever.
- API Resources handle serialization/presentation.
- Frontend components handle focused UI responsibilities.

A class should not accumulate unrelated responsibilities simply because they are convenient to place together.

## Open/Closed Principle

The strongest example is campaign simulation.

```text
CampaignSimulationStrategy
       ↑
       +-- PercentageDiscountStrategy
       +-- DoublePointsStrategy
```

The factory receives a mapping of campaign types to strategies.

Adding another campaign type should primarily involve:

1. new campaign parameter DTO;
2. new strategy;
3. enum value;
4. provider registration;
5. request mapping/validation;
6. tests;
7. frontend support where needed.

Existing shared simulation orchestration should not need to be rewritten to implement the new mathematical model.

Recommendations use the same principle:

```text
LeverAnalyzer
    ↑
    +-- DiscountLeverAnalyzer
    +-- PointsMultiplierLeverAnalyzer
    +-- FixedCostLeverAnalyzer
    +-- AudienceLeverAnalyzer
```

The recommendation engine iterates analyzers without knowing their formulas.

## Liskov Substitution Principle

Campaign strategies implement the same simulation contract and return the same `SimulationResultDTO`.

The rest of the application can consume either strategy through the common contract.

Likewise, repository implementations satisfy `HistoricalDataRepository`.

## Interface Segregation Principle

Interfaces are focused:

- `CampaignSimulatorInterface`
- `SimulationCalculationTrailBuilderInterface`
- `SimulationInsightCalculatorInterface`
- `CampaignRecommendationEngineInterface`
- `HistoricalDataRepository`
- `CampaignSimulationStrategy`
- `LeverAnalyzer`

Interfaces exist where they create useful substitutability or decoupling.

## Dependency Inversion Principle

The domain depends on abstractions where replacement matters.

Example:

```text
Campaign domain
      ↓
HistoricalDataRepository
      ↑
JsonHistoricalDataRepository
CachedHistoricalDataRepository
```

The domain does not directly instantiate the JSON data source.

Laravel providers perform the concrete wiring.

## DRY

Important examples:

- shared simulation result assembly in `AbstractCampaignSimulationStrategy`;
- shared formatting helpers for recommendation values;
- centralized frontend formatters;
- typed shared API models;
- reusable UI components;
- centralized API client behavior.

### DRY Warning

DRY does **not** mean extracting every repeated line into a generic abstraction.

Do not merge unrelated concepts merely because they look similar.

The abstraction must represent a real shared responsibility.

## KISS

The project favors simple deterministic logic over speculative infrastructure.

Examples:

- JSON data source for the challenge;
- no database persistence for simulations;
- no ML;
- no AI;
- request-scoped memoization rather than distributed caching for pure arithmetic;
- explicit DTOs rather than a generic data abstraction.

## Separation of Concerns

The frontend should not calculate financial results.

The backend should not contain Vue/UI concerns.

Domain services should not serialize HTTP responses.

Infrastructure should not leak into domain rules.

## Explainability

A major engineering/product principle is that a merchant should be able to understand why the application produced a result.

This is why the backend exposes:

- assumptions;
- calculation steps;
- health driver message;
- action message;
- orders context;
- recommendation messages.

## Explicitness Over Cleverness

When a business rule is important, prefer code that makes it easy to inspect and test.

This is especially important for financial calculations.

## Determinism

The simulation and recommendation engine should be deterministic for the same input, configuration and historical data.

Do not introduce randomness into campaign results.

## Avoid False Precision

Recommendations use realistic commercial thresholds and rounded values instead of presenting mathematically precise but operationally meaningless suggestions.

## When Adding Abstractions

Before adding an interface, factory, strategy, repository or service, answer:

1. What responsibility is being isolated?
2. What concrete variation exists?
3. What dependency is being inverted?
4. Does the abstraction make testing or replacement easier?
5. Does it reduce or increase cognitive load?

If there is no good answer, prefer the simpler implementation.
