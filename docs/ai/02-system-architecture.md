# 02 — System Architecture

## Architectural Shape

The system is a frontend/backend application.

```text
Vue 3 / TypeScript
        |
        | JSON HTTP API
        v
Laravel Controllers
        |
        v
Application/domain services
        |
        +--------------------+
        |                    |
        v                    v
Campaign simulation      Recommendation engine
        |                    |
        v                    v
Campaign strategies     Lever analyzers
        |
        v
HistoricalDataRepository
        |
        v
Cached JSON repository
```

## Dependency Direction

The intended direction is:

```text
HTTP
  ↓
Application services
  ↓
Domain concepts
  ↓
Interfaces
  ↓
Infrastructure implementations
```

The Campaigns domain should not depend on a concrete JSON repository.

The repository interface belongs to the Campaigns domain; the JSON and cached implementations belong to Infrastructure.

## Domain Separation

There are two meaningful domain areas:

### Campaigns

Responsible for:

- campaign parameters;
- merchant assumptions;
- scenario generation;
- simulation;
- calculation results;
- calculation trail;
- simulation insights.

### Recommendations

Responsible for:

- deciding which levers are applicable;
- probing changes;
- determining whether a change is actionable;
- producing merchant-facing recommendation data.

Recommendations depend on Campaigns, not the other way around.

This one-way relationship is intentional.

## Infrastructure

Infrastructure provides the current historical data implementation:

```text
HistoricalDataRepository
        ^
        |
CachedHistoricalDataRepository
        |
JsonHistoricalDataRepository
        |
merchant_profiles.json
```

The domain does not know whether the data comes from JSON, a database, an API, or another source.

## Dependency Injection

Laravel service providers wire interfaces to implementations.

Important providers:

- `CampaignServiceProvider`
- `RecommendationServiceProvider`

This keeps construction/wiring out of business classes.

## Backend as Financial Source of Truth

The frontend must never become the authoritative implementation of financial calculations.

The frontend displays backend results.

If a financial formula changes, update the backend calculation logic and its tests first, then update the frontend only as necessary to represent the changed contract.

## Stateless Simulation

Simulation arithmetic is deterministic and does not require persistent state.

`SimulationMemo` is request-scoped and exists only to avoid duplicate probe calculations during recommendation analysis.

It is not a persistent cache.
