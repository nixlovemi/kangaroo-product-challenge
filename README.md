# Campaign Advisor

A pre-launch decision-support tool that helps merchants understand the economics of a promotion before launching it.

## The Problem

Merchants can easily evaluate a promotion based on expected revenue or number of orders, but those metrics alone don't answer an important question:

> **"What does this promotion need to achieve to be worth the investment?"**

A discount can increase sales while still producing little or no additional profit once the discount and campaign costs are taken into account.

The goal of this project is to make those trade-offs explicit before a promotion is launched.

## Who Experiences This Problem?

The target user is a merchant running promotional or loyalty campaigns, particularly small and medium-sized businesses that may not have sophisticated financial modeling tools available.

The tool is designed for a quick decision-making workflow rather than detailed financial analysis.

## The Solution

The Promotion Simulator allows a merchant to configure a proposed promotion using a small set of assumptions:

* Target customers
* Average order value
* Gross margin
* Discount percentage
* Campaign cost
* Expected conversion rate

The simulator then estimates:

* Expected incremental purchases
* Incremental revenue
* Discount cost
* Estimated gross profit
* Campaign cost
* Net impact
* ROI
* Break-even conversion rate

The calculations are intentionally transparent so the merchant can understand how each assumption affects the result.

The primary experience is designed to answer the question quickly, while still allowing the user to inspect the underlying numbers.

## Key Product Decisions

### Profitability over revenue

The tool focuses on the economic impact of the promotion rather than simply showing how much revenue it could generate.

### Transparent assumptions

The simulator does not attempt to predict customer behavior without supporting data. Conversion and other inputs are explicit assumptions provided by the merchant.

### Decision support, not decision making

The tool presents the financial implications and trade-offs, but leaves the final decision to the merchant.

### Focused MVP

The MVP intentionally supports one simple promotion model and a limited number of variables.

The goal was to validate the core decision-making experience before introducing more complex campaign types or optimization logic.

## Key Technical Decisions

* **Laravel** for the backend API and business logic.
* **Nuxt/Vue** for the frontend.
* Financial calculations are handled by a dedicated backend simulation service to keep the calculation logic centralized and independently testable.
* Structured inputs and domain concepts are represented using DTOs and enums where appropriate.
* The frontend is responsible for presenting the simulation results, while the backend remains the source of truth for the calculations.

## What I Intentionally Left Out

* **AI-driven recommendations:** I intentionally did not use AI to make the decision for the merchant. The goal was to provide a transparent analysis based on explicit assumptions, allowing the merchant to understand the trade-offs and make the final decision.
* **Advanced visualizations:** I kept the main experience focused on a quick, actionable answer. More detailed analytical views could be added later, but the MVP prioritizes clarity over information density.
* **Export and print:** I did not include PDF/export/print functionality in the MVP, as the primary use case is a quick decision directly within the product.
* **Advanced forecasting and attribution:** I avoided predicting customer behavior or claiming that campaign results would be caused by the promotion without historical data or an experimental framework.
* **Customer-level segmentation:** The MVP does not attempt to determine which individual customers should receive the promotion.
* **Campaign execution:** The tool only evaluates a proposed promotion; it does not create, launch, or manage the campaign.
* **Complex loyalty-point economics:** Points, redemption behavior, breakage, and deferred rewards were intentionally left out to keep the initial model understandable and focused.

## What I Would Improve With More Time

* **Scenario and sensitivity analysis:** Allow merchants to evaluate multiple combinations of discount, conversion rate, campaign cost, and average order value, making the simulation more adaptable to different campaign strategies and merchant profiles.
* **Historical benchmarks:** Use real campaign data to replace generic assumptions with merchant-specific benchmarks.
* **Saved simulations:** Allow merchants to save and compare different promotion scenarios before launching a campaign.
* **Actual vs. simulated results:** After a campaign ends, compare the original simulation with actual results to help merchants improve future assumptions.

## Future Direction

I see the product evolving from a transparent simulation tool into a more data-driven decision-support system.

A possible progression would be:

**Explicit assumptions → Historical benchmarks → Merchant-specific insights → Recommendations**

The important distinction is that recommendations would come from actual campaign data rather than attempting to predict outcomes without sufficient evidence.

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
