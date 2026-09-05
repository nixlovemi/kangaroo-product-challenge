# 08 — Calculation Model

## Objective

This document is the authoritative explanation of the economic model currently implemented by the backend.

All rates/percentages in DTOs and domain calculations use a **0–100 percentage scale** unless explicitly converted to a 0–1 rate.

## Baseline Orders

```text
baseline orders =
audience size × historical conversion rate
```

Example:

```text
1,000 × 4.8% = 48
```

## Campaign Orders

```text
campaign orders =
audience size × campaign conversion rate
```

## Incremental Orders

```text
incremental orders =
max(campaign orders − baseline orders, 0)
```

This is important: campaign orders are not treated as entirely incremental.

## Incremental Revenue

```text
incremental revenue =
incremental orders × average order value
```

## Contribution Per Order

For the shared model:

```text
contribution per order =
average order value × gross margin
```

## Incremental Contribution

```text
incremental contribution =
incremental orders × contribution per order
```

## Net Impact

```text
net impact =
incremental contribution
− incentive cost
− fixed campaign cost
```

## ROI

Total campaign cost is:

```text
incentive cost + fixed campaign cost
```

When total campaign cost is greater than zero:

```text
ROI =
net impact / total campaign cost × 100
```

When total campaign cost is zero, ROI is `null`.

## Percentage Discount Incentive

For a percentage discount:

```text
incentive cost =
campaign orders × average order value × discount rate
```

The discount applies to campaign orders, not only incremental orders.

This is an intentional conservative cost assumption.

## Double Points Incentive

Base points:

```text
base points per order =
average order value × points earned per currency
```

Incremental points from a multiplier:

```text
incremental points =
base points × (multiplier − 1)
```

Expected incentive cost:

```text
campaign orders
× incremental points per order
× redemption rate
× cost per point
```

## Break-even Conversion

The strategies solve for the campaign conversion rate at which net impact reaches zero.

The exact equation depends on the incentive model.

The result exposed to the API is expressed as a percentage, e.g. `6.82`, not a fraction such as `0.0682`.

## Health Status

The shared strategy base class uses:

```text
healthy:
    net impact > 0
    AND campaign conversion >= break-even × 1.2

caution:
    net impact >= 0
    OR campaign conversion >= break-even

risky:
    otherwise
```

## Scenario Model

Given historical conversion `H` and historical lift `L`:

```text
scenario conversion =
H × (1 + (L × scenario multiplier) / 100)
```

Multipliers:

```text
conservative = 0.5
expected     = 1.0
strong       = 1.5
```

Custom scenarios bypass this formula and use the merchant-provided conversion rate.

## Rounding

The backend generally calculates using unrounded values and rounds the result to two decimal places at the result boundary.

The calculation trail is designed to stay consistent with the final DTO values.

Do not introduce intermediate rounding without a deliberate reason because it can create inconsistencies between displayed steps and final results.

## Important Assumptions

The model assumes:

- campaign conversion is a total conversion rate;
- incremental orders are campaign orders above historical baseline;
- gross margin represents contribution after product costs;
- incentive cost is borne according to the strategy-specific formula;
- fixed campaign cost is independent of response;
- no causal attribution is claimed;
- no ML forecast is performed.

## Model Invariants

Any future change must preserve or explicitly reconsider:

1. `incrementalOrders` cannot be negative.
2. Percentages remain consistently represented.
3. Backend remains the source of truth.
4. ROI is undefined (`null`) when total campaign cost is zero.
5. Break-even logic must remain consistent with net-impact logic.
6. Calculation-trail explanations must agree with the result DTO.
