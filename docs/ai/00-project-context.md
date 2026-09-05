# 00 — Project Context

## Objective

Campaign Advisor is a pre-launch decision-support application for merchants.

Its central question is:

> **What does this promotion need to achieve to be worth the investment?**

The application estimates campaign economics before launch and makes the assumptions and calculations inspectable.

## Repository Structure

```text
kangaroo-product-challenge/
├── backend/                 # Laravel API
├── frontend/                # Vue 3 + TypeScript + Vite
├── README.md                # Interview/product-facing project README
└── docs/ai/                 # This AI knowledge base
```

## Technology

### Backend

- PHP
- Laravel
- PHPUnit
- Laravel HTTP Resources
- Form Requests
- dependency injection through service providers
- PSR-style namespaces and typed DTOs
- JSON historical-data source for the challenge

### Frontend

- Vue 3
- TypeScript
- Vite
- Vitest
- Vue Test Utils
- `@material/web`

## Runtime Relationship

```text
Browser
  |
  | HTTP / JSON
  v
Vue frontend
  |
  | X-API-Key
  v
Laravel API
  |
  +--> Campaign domain
  |
  +--> Recommendation domain
  |
  +--> Historical-data repository
  |
  +--> JSON fixture / cache
```

## Product Capability

A merchant:

1. selects a merchant profile;
2. configures a campaign;
3. optionally provides a custom conversion estimate;
4. requests an analysis;
5. compares conservative, expected and strong-response scenarios;
6. can inspect the assumptions;
7. can inspect a calculation trail;
8. can inspect recommendation levers;
9. reviews the final decision summary.

## Campaign Types

Current supported campaign types:

- `percentage_discount`
- `double_points`

These are represented by `CampaignType` and implemented through separate simulation strategies.

## Scenario Types

Current supported scenarios:

- `conservative`
- `expected`
- `strong_response`
- `custom`

The first three are derived from historical campaign lift:

- conservative = 50% of historical lift
- expected = 100% of historical lift
- strong response = 150% of historical lift
- custom = explicitly supplied total campaign conversion rate

## Recommendation Types

The recommendation engine currently evaluates:

- discount percentage
- points multiplier
- fixed campaign cost
- audience size

A recommendation can be:

- `actionable`
- `implausible`
- `infeasible`

## Persistence

Campaign simulations are not persisted.

Merchant historical profiles are read from:

`backend/database/data/merchant_profiles.json`

The repository is abstracted behind `HistoricalDataRepository`, making the infrastructure replaceable.

## Important Design Intent

The project intentionally favors:

- explicit assumptions
- deterministic calculations
- explainability
- separation of concerns
- backend-owned business logic
- small, composable domain services
- realistic recommendations instead of mathematically meaningless suggestions

It intentionally avoids pretending that the system can predict customer behavior with certainty.
