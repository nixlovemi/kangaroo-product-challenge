# 17 — Frontend File Reference

## Root

### `src/main.ts`

Vue application entry point.

### `src/App.vue`

Application composition root.

## API

### `src/api/campaignSimulationClient.ts`

HTTP client for merchant profile and campaign simulation APIs.

Contains:

- `CampaignSimulationApiError`
- `CampaignSimulationClient`

## Composables

### `src/composables/useCampaignAdvisor.ts`

Main application state/orchestration composable.

## Types

### `src/types/campaign.ts`

Shared TypeScript model of:

- campaign types;
- scenarios;
- health statuses;
- request body;
- merchant data;
- simulation results;
- recommendations;
- calculation steps.

## Configuration

### `src/config/app.ts`

Environment-driven:

- API base URL;
- API key;
- locale.

Defaults are intended for the local challenge/demo environment.

## Content

### `src/content/campaignCopy.ts`

Centralized campaign-specific explanatory copy.

## Formatters

### `src/formatters/campaignFormatters.ts`

Shared display formatting for:

- currency;
- percentages;
- integers;
- scenario names.

Formatter behavior should be tested.

## Services

### `src/services/logger.ts`

Frontend logging abstraction.

## Components

### Root/application

- `AppShell.vue`
- `WizardHeader.vue`

### Steps

- `MerchantStep.vue`
- `CampaignDraftStep.vue`
- `AnalysisStep.vue`
- `ReviewStep.vue`

### Analysis

- `ScenarioSelector.vue`
- `ScenarioSummaryCard.vue`
- `MerchantAssumptionsAccordion.vue`
- `CalculationTrailAccordion.vue`
- `RecommendationsAccordion.vue`

### Library

- `HealthBadge.vue`
- `MetricCard.vue`
- `Modal.vue`
- `SegmentedControl.vue`
- `WizardActions.vue`

## Tests

Component tests exist for major components, including:

- `CampaignDraftStep.spec.ts`
- `MerchantStep.spec.ts`
- `AnalysisStep` behavior where covered by child components
- `ReviewStep.spec.ts`
- `ScenarioSelector.spec.ts`
- `ScenarioSummaryCard.spec.ts`
- `RecommendationsAccordion.spec.ts`
- `CalculationTrailAccordion.spec.ts`
- `HealthBadge.spec.ts`
- `MetricCard.spec.ts`
- `Modal.spec.ts`
- `SegmentedControl.spec.ts`
- `WizardActions.spec.ts`
- `useCampaignAdvisor.spec.ts`
- `campaignFormatters.spec.ts`
- `logger.spec.ts`

## Component Design Rule

If a component is reusable and generic, keep it under `components/library`.

If it understands campaign-specific concepts, keep it under `components/analysis` or `components/steps`.

When introducing a new component, document:

- responsibility;
- inputs;
- outputs/events;
- dependencies;
- related tests.
