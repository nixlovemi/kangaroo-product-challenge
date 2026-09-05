# 01 — Business and Product

## Problem

Merchants can evaluate promotions using revenue and order volume, but these numbers do not necessarily answer whether the promotion is economically worthwhile.

A campaign can increase sales while still being unattractive after considering:

- baseline purchases that would have happened anyway;
- incentive cost;
- fixed campaign cost;
- gross margin.

The product therefore focuses on **incremental economics**, not raw campaign revenue.

## Target User

The target user is a merchant running loyalty or promotional campaigns, especially a merchant who needs a quick answer without building a financial model manually.

## Core Product Question

> What does this campaign need to achieve to break even or reach the desired ROI?

## Product Philosophy

### Decision support, not decision replacement

The application explains the economics and presents possible parameter changes. The merchant remains responsible for the final decision.

AI is deliberately not used to decide whether a campaign should launch.

### Transparent assumptions

Conversion is an assumption. Historical merchant data is used to create scenario estimates, but the product should not imply certainty.

### Profitability over vanity metrics

The application prioritizes:

- incremental orders
- incremental revenue
- incentive cost
- incremental contribution
- fixed campaign cost
- net impact
- ROI
- break-even conversion

## Positioning

The project is intentionally narrower than a general loyalty-management platform.

The decision point is:

> **I am about to launch this specific promotion. What would it need to achieve to make economic sense?**

This differs from program-level ROI estimation and post-campaign reporting.

## UX Principle

The main analysis should answer the merchant quickly.

Detailed information is available through expandable analytical sections rather than forcing every calculation into the primary visual hierarchy.

This explains why the implementation uses:

- a scenario selector;
- a prominent summary card;
- assumptions accordion;
- calculation-trail accordion;
- recommendations accordion.

## Intentional Scope Exclusions

The project does not attempt to:

- use AI to make decisions;
- perform customer-level causal attribution;
- predict conversion with ML;
- execute campaigns;
- persist campaign simulations;
- implement complex loyalty accounting;
- create advanced reporting dashboards;
- provide PDF/print/export;
- turn the UI into a dense analytics dashboard.

## Future Direction

The intended progression is:

```text
Explicit assumptions
        ↓
Historical benchmarks
        ↓
Merchant-specific insights
        ↓
More sophisticated recommendations
```

Any future recommendation intelligence should be grounded in real historical data rather than unsupported prediction.
