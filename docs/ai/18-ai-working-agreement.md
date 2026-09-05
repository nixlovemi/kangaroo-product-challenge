# 18 — AI Working Agreement

## Purpose

This document defines how an AI assistant should work on this project.

A new AI should read this file after reading `INDEX.md`, `00-project-context.md` and the relevant technical documents.

## Rule 1 — Understand Before Changing

Before modifying code:

1. identify the relevant domain/feature;
2. read the relevant documentation;
3. inspect the current implementation;
4. inspect related tests;
5. trace the request/data flow;
6. only then propose or implement a change.

Do not modify code based only on a filename or assumption.

## Rule 2 — Documentation Is Part of the System

Every meaningful change requires a documentation review.

After changing code:

1. identify which knowledge-base documents are affected;
2. update them;
3. check `15-change-impact-matrix.md`;
4. search for stale names/formulas/flows if necessary.

## Rule 3 — Do Not Invent Requirements

If the code or documentation does not establish a requirement:

- do not invent one;
- identify the ambiguity;
- propose options;
- ask for clarification when the decision materially changes behavior.

## Rule 4 — Preserve Domain Boundaries

Do not move logic merely because another layer appears more convenient.

### Backend

- HTTP concerns stay in HTTP.
- Business rules stay in Domain/services/strategies.
- Data-source details stay in Infrastructure.
- Serialization stays in Resources/presenters.

### Frontend

- UI concerns stay in components.
- cross-component state belongs in the composable;
- API transport belongs in the API client;
- formatting belongs in formatter helpers;
- financial calculations belong in the backend.

## Rule 5 — Financial Logic Is High Risk

Before changing any formula:

1. read `08-calculation-model.md`;
2. inspect the strategy implementation;
3. inspect the relevant tests;
4. verify break-even and ROI implications;
5. update calculation-trail behavior if needed;
6. update documentation.

Do not silently change economic assumptions.

## Rule 6 — Respect SOLID/DRY/KISS

Prefer the existing design language.

Before adding a class, interface, factory or abstraction, explain why it is needed.

Do not:

- create abstractions without variation;
- duplicate existing formatting/business logic;
- put unrelated responsibilities into existing classes;
- introduce a new state-management system without a concrete requirement.

## Rule 7 — Open/Closed

When adding a new campaign type:

- prefer a new strategy;
- preserve the common simulation contract;
- register the strategy through the existing factory wiring;
- avoid branching campaign-specific mathematics throughout unrelated classes.

When adding a recommendation lever:

- implement `LeverAnalyzer`;
- keep its logic inside the analyzer;
- let the engine discover/use it through the existing analyzer collection.

## Rule 8 — API Contract

Treat the backend response and frontend TypeScript types as one contract.

If the backend changes:

- update API documentation;
- update TypeScript types;
- update API client behavior if necessary;
- update affected components;
- update feature/component tests.

## Rule 9 — Tests

Do not consider a change complete because the application starts.

Run the relevant tests.

For financial changes, prioritize unit tests and API feature tests.

For UI changes, prioritize component tests and the application build.

## Rule 10 — Do Not Over-Engineer

This is a product challenge project.

A technically impressive abstraction that makes the product harder to understand is not automatically an improvement.

Prefer the smallest design that:

- preserves correctness;
- preserves boundaries;
- is testable;
- is understandable;
- supports the actual product requirement.

## Rule 11 — Recommendations Must Remain Explainable

The current recommendation engine is deterministic.

Do not introduce AI-generated recommendations as a replacement for the existing economic reasoning unless explicitly requested.

If a future system uses AI, it must not obscure the underlying financial model.

## Rule 12 — Do Not Fake Precision

If a recommendation is mathematically possible but commercially unrealistic, preserve the distinction between:

- actionable;
- implausible;
- infeasible.

Do not turn every mathematical solution into a merchant recommendation.

## Rule 13 — Keep the Backend as Source of Truth

Never duplicate the financial model in TypeScript just to make a UI feature easier.

If the UI needs a financial value that is not currently returned by the API, consider extending the backend contract.

## Rule 14 — Preserve Existing Intentional Comments

The code contains comments explaining important architectural/product decisions.

Before removing such a comment, determine whether the underlying decision is still valid.

If the decision changed, update both code and documentation.

## Rule 15 — When You Find a Better Design

Do not silently refactor architecture during an unrelated task.

Explain:

- current design;
- problem with current design;
- proposed design;
- trade-offs;
- affected files/tests/docs.

Then implement only when the change is appropriate.

## Rule 16 — Completion Checklist

Before saying a task is complete:

- [ ] code implemented;
- [ ] relevant tests updated;
- [ ] relevant tests pass;
- [ ] API contract checked;
- [ ] frontend types checked;
- [ ] no duplicated business logic introduced;
- [ ] architecture boundaries preserved;
- [ ] documentation updated;
- [ ] change-impact matrix reviewed;
- [ ] no stale references remain.

## Golden Rule

> **Understand the product intent, preserve the domain boundaries, change the smallest necessary surface, test the behavior, and update the knowledge base before finishing.**
