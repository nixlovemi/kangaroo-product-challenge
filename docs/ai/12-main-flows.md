# 12 — Main Flows

## Flow 1 — Initial Load

```text
App.vue
  ↓
useCampaignAdvisor()
  ↓
default merchantId = 101
  ↓
loadMerchantOverview()
  ↓
GET /merchants/101/profile
  ↓
merchantOverview
```

## Flow 2 — Merchant Selection

```text
MerchantStep
  ↓ choose merchant
AppShell
  ↓ update:merchant
useCampaignAdvisor.selectMerchant()
  ↓
clear previous merchant overview
  ↓
GET merchant profile
  ↓
new assumptions/default conversion
```

## Flow 3 — Campaign Configuration

```text
CampaignDraftStep
  ↓
user changes draft
  ↓
AppShell translates events
  ↓
useCampaignAdvisor state
```

No financial calculation occurs in the browser.

## Flow 4 — Scenario Analysis

```text
User clicks Start campaign analysis
        ↓
useCampaignAdvisor.analyze()
        ↓
POST /campaigns/simulate/scenarios
        ↓
SimulationRequest validation
        ↓
CampaignAdvisorService
        ↓
CampaignSimulationService
        ↓
historical merchant profile
        ↓
three preset scenarios
        ↓
campaign strategy
        ↓
SimulationResultDTO
        ↓
RecommendationEngine
        ↓
CampaignAdviceResource
        ↓
frontend analysis state
        ↓
AnalysisStep
```

## Flow 5 — Custom Scenario

```text
ScenarioSelector
  ↓
open custom modal
  ↓
user enters total campaign conversion rate
  ↓
setCustomConversionRate()
  ↓
applyCustomScenario()
  ↓
analyze()
  ↓
backend receives campaign_conversion_rate
  ↓
custom scenario appended
  ↓
frontend selects custom scenario
```

## Flow 6 — Recommendation Analysis

For each scenario:

```text
ScenarioResult
   ↓
CampaignRecommendationEngine
   ↓
for each applicable LeverAnalyzer
   ↓
SimulationMemo
   ↓
probe simulation(s)
   ↓
RecommendationDTO
   ↓
RecommendationSetDTO
```

The engine does not know the mathematical details of each lever.

## Flow 7 — Calculation Explanation

```text
SimulationResultDTO
       ↓
SimulationCalculationTrailBuilder
       ↓
CalculationStepDTO[]
       ↓
SimulationResultResource
       ↓
calculation_steps
       ↓
CalculationTrailAccordion
```

This is deliberately backend-generated so displayed explanations remain tied to the authoritative result.

## Flow 8 — Review

```text
AnalysisStep
  ↓ Continue
AppShell
  ↓
ReviewStep
  ↓
selected/current scenario
  ↓
final decision summary
```

The review step does not perform a second financial calculation.

## Flow 9 — Reset

```text
ReviewStep
  ↓ Start over
AppShell
  ↓
useCampaignAdvisor.reset()
  ↓
merchant step
analysis cleared
error cleared
selected scenario = expected
```
