# 07 — API Contract

## Base URL

Default frontend configuration:

`http://127.0.0.1:8000/api/v1`

Configured by:

`frontend/src/config/app.ts`

## Authentication

Protected challenge endpoints require:

```http
X-API-Key: <configured key>
```

The API key middleware is:

`backend/app/Http/Middleware/ApiKeyMiddleware.php`

Routes are also throttled at:

`60 requests / minute`

## Endpoint: Merchant Profile

```http
GET /api/v1/merchants/{merchant}/profile
```

Returns merchant information and the default expected campaign conversion rate.

Example conceptual response:

```json
{
  "success": true,
  "message": "Merchant profile retrieved.",
  "data": {
    "merchant": {
      "id": 101,
      "name": "Atelier Nord Cafe",
      "currency": "CAD"
    },
    "assumptions": {
      "average_order_value": 68.5,
      "gross_margin_percentage": 58,
      "historical_conversion_rate": 4.8,
      "historical_campaign_lift_percentage": 42
    },
    "expected_conversion_rate": 6.82
  }
}
```

## Endpoint: Single Simulation

```http
POST /api/v1/campaigns/simulate
```

Request:

```json
{
  "merchant_id": 101,
  "audience_size": 1000,
  "fixed_campaign_cost": 250,
  "campaign_type": "percentage_discount",
  "campaign_conversion_rate": null,
  "parameters": {
    "discount_percentage": 10
  }
}
```

Supported parameter objects:

```json
{ "discount_percentage": 10 }
```

or:

```json
{ "points_multiplier": 2 }
```

## Endpoint: Scenario Analysis

```http
POST /api/v1/campaigns/simulate/scenarios
```

Uses the same request structure as single simulation.

Returns:

- merchant information;
- merchant assumptions;
- fixed campaign cost;
- three preset scenarios;
- optional custom scenario;
- simulation result for every scenario;
- recommendation set for every scenario.

## Standard Envelope

Successful responses:

```json
{
  "success": true,
  "message": "...",
  "data": {}
}
```

Validation/domain errors:

```json
{
  "success": false,
  "message": "...",
  "data": null,
  "errors": {}
}
```

## Validation

Current important boundaries:

- merchant ID: integer >= 1;
- audience: integer 1..1,000,000;
- fixed campaign cost: 0..999999.99;
- campaign type: known `CampaignType`;
- campaign conversion: 0..100 when supplied;
- discount: 0..100;
- points multiplier: 1..10.

## Error Statuses

- `401` — missing/invalid API key;
- `404` — merchant profile not found;
- `422` — validation failure;
- `500` — unexpected server-side failure.

## Contract Rule

If the response shape changes, review all of:

- `07-api-contract.md`
- `11-frontend-state-api-types.md`
- affected frontend components
- backend feature tests
- `15-change-impact-matrix.md`

The frontend TypeScript types are part of the effective API contract.
