# 15 — Change Impact Matrix

## Purpose

Use this document before and after every change.

The goal is to prevent code and knowledge-base drift.

## General Rule

**Every meaningful change requires a documentation review.**

Update the documentation in the same work session/PR whenever the behavior or design described by it changes.

## Matrix

| Change | Review / Update |
|---|---|
| Product behavior | `01-business-and-product.md`, `00-project-context.md` |
| New campaign type | `04-campaigns-domain.md`, `08-calculation-model.md`, `07-api-contract.md`, `11-frontend-state-api-types.md`, relevant file references |
| Campaign formula | `08-calculation-model.md`, `04-campaigns-domain.md`, `13-testing-and-quality.md` |
| Break-even rule | `08-calculation-model.md`, tests |
| Health-status rule | `08-calculation-model.md`, `04-campaigns-domain.md`, frontend presentation docs |
| New scenario type | `04-campaigns-domain.md`, `12-main-flows.md`, API/types docs |
| Recommendation lever | `05-recommendations-domain.md`, API/types docs, tests |
| Recommendation threshold | `05-recommendations-domain.md`, `06-data-and-infrastructure.md`, tests |
| API endpoint | `07-api-contract.md`, `12-main-flows.md`, frontend API client/types |
| API request field | `07-api-contract.md`, `11-frontend-state-api-types.md`, validation tests |
| API response field | `07-api-contract.md`, `11-frontend-state-api-types.md`, consumers/tests |
| Authentication change | `07-api-contract.md`, backend architecture |
| Repository/data source | `06-data-and-infrastructure.md`, `02-system-architecture.md` |
| Merchant fixture change | `06-data-and-infrastructure.md`, affected tests |
| Cache behavior | `06-data-and-infrastructure.md` |
| New backend service | `03-backend-architecture.md`, `16-backend-file-reference.md` |
| New backend DTO | `04-campaigns-domain.md` or `05-recommendations-domain.md`, file reference |
| New backend interface | `14-design-principles.md`, relevant architecture doc |
| New frontend component | `10-frontend-components.md`, `17-frontend-file-reference.md` |
| Frontend state change | `09-frontend-architecture.md`, `11-frontend-state-api-types.md` |
| Formatter change | `09-frontend-architecture.md`, `17-frontend-file-reference.md` |
| UX flow change | `01-business-and-product.md`, `09-frontend-architecture.md`, `12-main-flows.md` |
| Testing strategy change | `13-testing-and-quality.md` |
| Architectural change | `02-system-architecture.md`, `03-backend-architecture.md` and/or `09-frontend-architecture.md` |
| SOLID/pattern decision change | `14-design-principles.md` |
| Known limitation removed/added | `00-project-context.md`, `01-business-and-product.md`, `18-ai-working-agreement.md` |

## New File Rule

Whenever a new meaningful class/module/component is added:

1. add it to the relevant file-reference document;
2. describe its responsibility;
3. document its relationships if non-trivial;
4. identify its tests;
5. identify which architectural principle it follows if relevant.

## Rename/Move Rule

When a file/class is renamed or moved:

- update every documentation reference;
- search the knowledge base for the old path/name;
- update architecture diagrams and flow descriptions.

## Formula Change Rule

Never update a financial formula without reviewing:

- implementation;
- unit tests;
- calculation-trail behavior;
- API response;
- frontend display;
- `08-calculation-model.md`.

## Contract Change Rule

For an API contract change, treat backend and frontend changes as one contract change.

Do not update only one side.

## Documentation Integrity Check

Before finishing a meaningful task, ask:

> If a new AI read only this knowledge base, would it understand the new behavior correctly?

If not, update the documentation before considering the task complete.
