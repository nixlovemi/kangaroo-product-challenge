# 05 — Recommendations Domain

## Objective

The Recommendations domain answers:

> If the current scenario does not meet the configured ROI target, is there a realistic parameter change that would make it viable?

It is an enrichment layer on top of Campaigns.

## Dependency Direction

```text
Recommendations
       ↓
Campaigns
```

Campaigns does not know about Recommendations.

`CampaignAdvisorService` is the orchestration boundary.

## CampaignAdvisorService

Responsibilities:

1. ask Campaigns to generate scenarios;
2. convert each scenario back into a fully populated `SimulationInputDTO`;
3. ask `CampaignRecommendationEngine` for recommendations;
4. combine simulation and recommendation results into `CampaignAdviceDTO`.

## CampaignRecommendationEngine

Runs all applicable `LeverAnalyzer` implementations.

If the current ROI already meets the target:

- no parameter changes are proposed;
- `alreadyMeetsTarget = true`.

Otherwise, each supported analyzer gets a chance to produce a recommendation.

Actionable recommendations are ordered before diagnostic outcomes.

## LeverAnalyzer Contract

Every lever analyzer implements:

- `supports(input)`
- `analyze(input, result, goal, memo)`

Returning `null` means the lever has nothing meaningful to say for that campaign.

This interface enables adding new levers without changing the recommendation engine's orchestration loop.

## Current Levers

### DiscountLeverAnalyzer

Used for percentage-discount campaigns.

It solves for the discount level required to reach the ROI target, rounds to a commercially sensible step, and rejects suggestions below the minimum viable discount.

### PointsMultiplierLeverAnalyzer

Used for double-points campaigns.

It solves for the required multiplier using the current incentive-cost slope and rejects values below the configured commercially viable floor.

### FixedCostLeverAnalyzer

Tests realistic reductions in fixed campaign cost rather than suggesting an artificially precise amount.

Configured probe percentages are:

- 50%
- 75%

If those do not work, it tests zero cost to diagnose whether fixed cost is actually the problem.

### AudienceLeverAnalyzer

Tests audience expansion using configured multiples.

Current probe multiples:

- 1.5×
- 2.0×

It avoids recommending audience growth when each extra customer costs more in incentive than the margin they generate.

## Recommendation Outcomes

### Actionable

Mathematically reachable and commercially plausible.

### Implausible

Mathematically reachable, but below a configured real-world floor.

Example: a discount so small that it would no longer read as a meaningful promotion.

### Infeasible

The lever cannot reach the target even at its valid extreme.

This distinction is important because the product should not force a fake recommendation when the correct diagnosis is that a different offer is needed.

## RecommendationGoalDTO

Configuration-driven thresholds include:

- target ROI;
- minimum viable discount;
- minimum viable points multiplier;
- fixed-cost probe percentages;
- audience probe multiples;
- maximum audience size.

These values come from `config/campaigns.php`.

## SimulationMemo

`SimulationMemo` is request-scoped memoization.

Recommendation analyzers repeatedly simulate probe inputs. The memo prevents identical probes from being recalculated within the same request.

It is deliberately not a persistent cache because simulation arithmetic is in-memory and deterministic.

## Recommendation Principle

Recommendations are not intended to be AI predictions.

They are deterministic consequences of the economic model and configured commercial constraints.
