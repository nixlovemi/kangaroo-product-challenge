# AI Knowledge Base — Campaign Advisor

## Purpose

This directory is the **primary technical and product knowledge base for AI-assisted development** of the Campaign Advisor project.

The project has two applications:

* `backend/` — Laravel API, domain logic, simulation engine, recommendation engine, historical-data access and API presentation.
* `frontend/` — Vue 3 + TypeScript + Vite application that implements the merchant-facing wizard and analysis experience.

The root `README.md` is intentionally kept short. It provides a high-level summary of the project, including the problem, target user, solution, key product and technical decisions, and the main trade-offs.

This directory provides the deeper context required to understand, maintain and extend the actual implementation.

A new AI assistant should use this documentation as the primary reference before making changes to the project.

## Required Reading Order

Start with these files:

1. `00-project-context.md`
2. `01-business-and-product.md`
3. `02-system-architecture.md`
4. `12-main-flows.md`
5. `18-ai-working-agreement.md`

Then read the area-specific documentation relevant to the task.

## Documentation Map

| File                             | Purpose                                                                 |
| -------------------------------- | ----------------------------------------------------------------------- |
| `00-project-context.md`          | Project identity, scope, stack and current implementation snapshot      |
| `01-business-and-product.md`     | Business problem, target user, product philosophy and intentional scope |
| `02-system-architecture.md`      | Overall architecture and dependency direction                           |
| `03-backend-architecture.md`     | Laravel layers, dependency injection and request flow                   |
| `04-campaigns-domain.md`         | Campaign simulation domain in detail                                    |
| `05-recommendations-domain.md`   | Recommendation/advisor domain in detail                                 |
| `06-data-and-infrastructure.md`  | Historical data, repository abstraction and cache                       |
| `07-api-contract.md`             | Endpoints, request/response contracts and authentication                |
| `08-calculation-model.md`        | Financial formulas, assumptions, scenario model and invariants          |
| `09-frontend-architecture.md`    | Vue application architecture and state flow                             |
| `10-frontend-components.md`      | Component responsibilities and relationships                            |
| `11-frontend-state-api-types.md` | Composable, API client and TypeScript contracts                         |
| `12-main-flows.md`               | End-to-end user and system flows                                        |
| `13-testing-and-quality.md`      | Testing strategy and important regression cases                         |
| `14-design-principles.md`        | SOLID, DRY, Open/Closed and other engineering practices used            |
| `15-change-impact-matrix.md`     | Which documentation must be reviewed when code changes                  |
| `16-backend-file-reference.md`   | Reference for backend classes, interfaces, enums and resources          |
| `17-frontend-file-reference.md`  | Reference for frontend components and modules                           |
| `18-ai-working-agreement.md`     | Rules an AI must follow when analyzing or changing the project          |
| `19-complete-code-inventory.md`  | Complete inventory of the current backend and frontend source files     |

## Living Documentation Rule

**Every code or product change must trigger a documentation review.**

When something changes:

1. Identify the documentation file(s) describing that behavior.
2. Update the documentation in the same change when the behavior, architecture, contract, formula, responsibility or assumption changes.
3. If the change creates a new concept, add it to the appropriate reference document.
4. Review `15-change-impact-matrix.md` to identify related documents.
5. Never allow documentation to intentionally describe behavior that no longer exists.

Documentation is part of the project's maintainability, not an optional afterthought.

## Source of Truth

When documentation and code disagree:

* The **current code is the source of truth for what is actually implemented**.
* The documentation is the source of truth for **documented intent, architectural rules and product decisions**, until the code is deliberately changed.
* If the conflict is meaningful, do not silently choose one. Explain the discrepancy and update the relevant documentation after the intended behavior is confirmed.

## How to Use This Knowledge Base

Use the documentation according to the type of task:

* **Understanding the project:** Start with `00-project-context.md`, `01-business-and-product.md` and `02-system-architecture.md`.
* **Changing backend behavior:** Read the relevant backend/domain documents and check `15-change-impact-matrix.md`.
* **Changing financial calculations:** Always read `08-calculation-model.md` before modifying calculation logic.
* **Changing recommendations:** Read `05-recommendations-domain.md` and the relevant campaign documentation.
* **Changing the API:** Read `07-api-contract.md` and the affected backend/frontend documentation.
* **Changing the frontend:** Read `09-frontend-architecture.md`, `10-frontend-components.md` and `11-frontend-state-api-types.md` as applicable.
* **Changing a user flow:** Read `12-main-flows.md` and the relevant product/domain documentation.
* **Adding or modifying tests:** Read `13-testing-and-quality.md`.
* **Making architectural changes:** Read `02-system-architecture.md`, `03-backend-architecture.md` and `14-design-principles.md`.
* **Before making any change:** Follow `18-ai-working-agreement.md`.

Do not read every document for every task. Read the minimum set required to understand the affected area, while following the impact guidance in `15-change-impact-matrix.md`.

## Documentation Philosophy

This knowledge base is intentionally more detailed than the root README.

The README answers:

> **What is this project, why was it built, and what are the main decisions?**

This knowledge base answers:

> **How does the current implementation work, why is it structured this way, and what rules should be preserved when changing it?**

The goal is not to duplicate the README. The goal is to provide enough context for an AI assistant to make informed changes without having to rediscover the project's architecture, business rules and design decisions from the code alone.