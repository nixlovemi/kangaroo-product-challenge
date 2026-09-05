# 09 — Frontend Architecture

## Objective

The frontend provides a fast, merchant-friendly campaign analysis workflow.

Technology:

- Vue 3
- TypeScript
- Vite
- Vitest

## Top-Level Structure

```text
src/
├── api/
├── components/
│   ├── analysis/
│   ├── library/
│   └── steps/
├── composables/
├── config/
├── content/
├── formatters/
├── services/
├── types/
├── App.vue
└── main.ts
```

## Application Composition

```text
App.vue
  ↓
useCampaignAdvisor()
  ↓
AppShell
  ↓
WizardHeader
  ↓
one of:
  MerchantStep
  CampaignDraftStep
  AnalysisStep
  ReviewStep
```

## App.vue

`App.vue` is the composition root.

It:

- creates the campaign-advisor composable;
- derives the campaign display name;
- passes state/actions into `AppShell`.

It should not become the place for business calculations.

## AppShell

`AppShell.vue` owns wizard-level composition.

It:

- determines which step is visible;
- groups draft state;
- translates grouped draft updates into composable state updates;
- passes data/actions to child steps.

## Steps

### MerchantStep

Selects the merchant.

### CampaignDraftStep

Collects:

- audience;
- fixed cost;
- campaign type;
- discount or points multiplier;
- optional custom conversion.

### AnalysisStep

Shows:

- scenario selector;
- selected scenario summary;
- merchant assumptions;
- calculation trail;
- recommendations.

### ReviewStep

Shows a final summary of the configured campaign and selected scenario.

## Analysis Components

Analysis is split into focused components:

- `ScenarioSelector`
- `ScenarioSummaryCard`
- `MerchantAssumptionsAccordion`
- `CalculationTrailAccordion`
- `RecommendationsAccordion`

## Library Components

Reusable UI primitives live under:

`components/library/`

Examples:

- `MetricCard`
- `HealthBadge`
- `SegmentedControl`
- `Modal`
- `WizardActions`

These components should remain generic rather than accumulating campaign-specific business rules.

## State Management

The application uses the custom composable:

`useCampaignAdvisor`

There is no external state-management library.

The composable owns:

- wizard step;
- merchant selection;
- campaign draft;
- loading;
- error;
- analysis response;
- selected scenario.

## Async Loading

Several larger components are loaded using `defineAsyncComponent`.

This keeps the initial application composition lighter and separates the feature areas.

## Formatting

Financial/display formatting belongs in:

`src/formatters/campaignFormatters.ts`

Do not reproduce currency/percentage formatting manually across components.

## Content

Merchant-facing explanatory copy that is shared across components belongs in:

`src/content/campaignCopy.ts`

## Logging

Client logging abstraction lives in:

`src/services/logger.ts`

Do not scatter raw logging behavior throughout the UI if the logger abstraction already covers the use case.
