# Promotion Simulator

A pre-launch decision-support tool that helps merchants understand the economics of a promotion before launching it.

## The Problem

Merchants can easily evaluate a promotion based on expected revenue or order volume, but these metrics alone don't answer an important question:

> **What does this promotion need to achieve to be worth the investment?**

A promotion can increase sales while generating little or no additional profit after accounting for discounts and campaign costs.

## Who Experiences This Problem?

The primary users are merchants running promotional or loyalty campaigns, particularly small and medium-sized businesses that need a quick way to evaluate the economics of a proposed offer.

## The Solution

The Promotion Simulator allows merchants to configure a promotion using business and campaign assumptions, then estimates its financial impact before launch.

The application provides:

* Expected incremental purchases and revenue
* Discount and campaign costs
* Estimated gross profit and net impact
* ROI
* Break-even conversion
* Multiple performance scenarios
* Recommendation insights based on the simulation

The calculations are transparent, allowing merchants to understand the assumptions behind the results rather than simply receiving a recommendation.

## Key Product Decisions

* **Profitability over revenue:** The primary question is whether the promotion is economically worthwhile, not simply whether it generates more sales.
* **Decision support, not decision making:** The tool helps merchants understand trade-offs while keeping the final decision with them.
* **Transparent assumptions:** The MVP does not rely on AI or unsupported predictions to determine campaign outcomes.
* **Scenario-based analysis:** Conservative, expected, strong, and custom scenarios provide different perspectives on potential outcomes.
* **Focused experience:** The main screen prioritizes a quick answer, with additional analytical details available when needed.

## Key Technical Decisions

* Laravel/PHP backend with Nuxt/Vue frontend.
* Clear separation between presentation, application, domain, and infrastructure concerns.
* Campaign and recommendation behavior is extensible through strategies and interfaces.
* Financial calculations are centralized in the backend and covered by automated tests.
* DTOs and enums are used to keep domain data explicit and structured.
* The frontend consumes the backend as the source of truth for financial results.

## What I Intentionally Left Out

* AI-driven decision making
* Advanced predictive modeling and customer-level attribution
* Campaign creation or execution
* Live Kangaroo integrations
* Export/print functionality
* Extensive charts and visualizations
* Complex loyalty-point accounting

These were intentionally excluded to keep the MVP focused on a fast, transparent pre-launch analysis.

## What I Would Improve With More Time

* More scenario and sensitivity analysis to better adapt the tool to different merchant profiles and campaign strategies.
* Historical campaign benchmarks to replace generic assumptions with merchant-specific data.
* Saved and comparable simulations.
* Actual vs. simulated campaign performance.
* Deeper recommendation capabilities based on real historical data.

## Implementation Notes

The project includes a complete backend API and Nuxt frontend, including campaign simulation strategies, scenario analysis, recommendation levers, validation, error handling, automated tests, and a calculation trail explaining how results are derived.

Detailed AI documentation is available in [`docs/ai/`](docs/ai/), starting with [`INDEX.md`](docs/ai/INDEX.md).

## Running the Project

### Backend

```bash
cd backend
composer install
php artisan serve
```

### Frontend

```bash
cd frontend
npm install
npm run dev
```
