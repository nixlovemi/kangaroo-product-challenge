# 13 — Testing and Quality

## Testing Stack

### Backend

PHPUnit through Laravel.

Test groups include:

- Feature
- Unit

### Frontend

Vitest + Vue Test Utils.

Component/spec tests exist for major UI and utility modules.

## Backend Test Priorities

The most important regression surface is the financial model.

Tests should protect:

- baseline calculation;
- campaign orders;
- incremental orders;
- incentive cost;
- contribution;
- net impact;
- ROI;
- break-even;
- health status;
- scenario generation;
- recommendation behavior.

## API Tests

Feature tests verify:

- successful simulation;
- both campaign types;
- scenario generation;
- custom scenario behavior;
- recommendation response shape;
- validation;
- unknown merchants;
- missing/invalid API keys;
- standard error envelopes.

## Recommendation Tests

Recommendation tests protect:

- actionable ordering;
- implausible vs infeasible diagnosis;
- target already met;
- discount recommendations;
- fixed-cost recommendations;
- audience recommendations;
- points multiplier recommendations;
- memo behavior.

## Infrastructure Tests

Repository tests protect:

- JSON loading;
- merchant lookup;
- missing merchant behavior;
- caching behavior.

## Frontend Tests

Component tests protect:

- component rendering;
- emitted events;
- scenario selection;
- recommendation presentation;
- calculation trail presentation;
- formatting;
- reusable UI behavior.

## Change Rule

Whenever a business rule changes:

1. update/extend backend unit tests;
2. update affected feature tests;
3. update calculation documentation;
4. update API types if the contract changes;
5. update frontend tests if presentation/behavior changes.

## Quality Principle

A passing test suite does not replace architectural review.

For every change, ask:

- Did responsibility move to the correct layer?
- Did we duplicate existing logic?
- Did we create a new coupling?
- Did the API contract change?
- Did documentation become stale?
