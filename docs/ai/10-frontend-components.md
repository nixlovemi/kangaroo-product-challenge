# 10 — Frontend Components

## Component Tree

```text
App
└── AppShell
    ├── WizardHeader
    ├── MerchantStep
    ├── CampaignDraftStep
    │   └── WizardActions
    ├── AnalysisStep
    │   ├── ScenarioSelector
    │   │   ├── SegmentedControl
    │   │   └── Modal
    │   ├── ScenarioSummaryCard
    │   ├── MerchantAssumptionsAccordion
    │   │   └── MetricCard
    │   ├── CalculationTrailAccordion
    │   └── RecommendationsAccordion
    └── ReviewStep
        ├── HealthBadge
        └── WizardActions
```

## Component Responsibilities

### AppShell.vue

Wizard orchestration and state-to-prop/event translation.

### WizardHeader.vue

Visual representation of the current wizard step.

### MerchantStep.vue

Merchant selection UI.

It emits a merchant selection event rather than directly calling the API.

### CampaignDraftStep.vue

Campaign configuration UI.

It is intentionally presentation-oriented. Business calculations are not performed here.

### AnalysisStep.vue

Composition layer for analysis-related components.

It derives:

- preset scenarios;
- custom scenario;
- currency;
- selected scenario data.

### ScenarioSelector.vue

Allows the user to:

- select preset scenarios;
- open custom conversion modal;
- apply a custom scenario.

Scenario labels and display values are formatted through the formatter module.

### ScenarioSummaryCard.vue

Presents the key metrics for the selected scenario and exposes an adjustment action.

### MerchantAssumptionsAccordion.vue

Shows historical merchant assumptions.

### CalculationTrailAccordion.vue

Shows the backend-generated calculation steps.

It deliberately does not recreate formulas in the frontend.

### RecommendationsAccordion.vue

Shows recommendation items and formats their values based on the API-provided `value_type`.

### ReviewStep.vue

Final review screen.

It uses the selected scenario rather than recalculating it.

## Reusable Library Components

### MetricCard

Generic metric presentation.

### HealthBadge

Maps health status to visual presentation.

### Modal

Generic modal shell.

### SegmentedControl

Generic segmented-option selection.

### WizardActions

Generic back/primary action footer.

## Component Design Rules

1. Components own presentation and local UI state.
2. Cross-step state belongs in `useCampaignAdvisor`.
3. API calls belong in the API client/composable boundary.
4. Business calculations belong in the backend.
5. Formatting helpers should be reused.
6. Generic components should not know about campaign-domain calculations.
7. Props and emitted events should remain typed.
