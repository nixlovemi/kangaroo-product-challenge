<?php

namespace Tests\Feature;

use Tests\TestCase;

final class CampaignSimulationApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['api.key' => 'test-api-key']);
    }

    public function test_it_returns_a_percentage_discount_simulation(): void
    {
        $response = $this->withHeader('X-API-Key', 'test-api-key')->postJson('/api/v1/campaigns/simulate', [
            'merchant_id' => 101,
            'audience_size' => 1000,
            'fixed_campaign_cost' => 0,
            'campaign_type' => 'percentage_discount',
            'parameters' => ['discount_percentage' => 10],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.health_status', 'caution')
            ->assertJsonPath('data.baseline_orders', 48)
            ->assertJsonStructure(['data' => [
                'baseline_orders',
                'campaign_orders',
                'incremental_orders',
                'incentive_cost',
                'net_impact',
                'break_even_conversion_rate',
                'roi',
                'health_status',
                'break_even_achievable',
            ]]);
    }

    public function test_it_returns_a_double_points_simulation_using_merchant_history(): void
    {
        $response = $this->withHeader('X-API-Key', 'test-api-key')->postJson('/api/v1/campaigns/simulate', [
            'merchant_id' => 101,
            'audience_size' => 1000,
            'fixed_campaign_cost' => 0,
            'campaign_type' => 'double_points',
            'parameters' => ['points_multiplier' => 2],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.incentive_cost', 177.42)
            ->assertJsonPath('data.net_impact', 623.54);
    }

    public function test_it_returns_fixed_scenarios_calculated_from_historical_lift(): void
    {
        $response = $this->withHeader('X-API-Key', 'test-api-key')->postJson('/api/v1/campaigns/simulate/scenarios', [
            'merchant_id' => 101,
            'audience_size' => 1000,
            'fixed_campaign_cost' => 0,
            'campaign_type' => 'percentage_discount',
            'parameters' => ['discount_percentage' => 10],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.merchant.id', 101)
            ->assertJsonPath('data.assumptions.historical_campaign_lift_percentage', 42)
            ->assertJsonCount(3, 'data.scenarios')
            ->assertJsonPath('data.scenarios.0.type', 'conservative')
            ->assertJsonPath('data.scenarios.0.campaign_conversion_rate', 5.81)
            ->assertJsonPath('data.scenarios.1.type', 'expected')
            ->assertJsonPath('data.scenarios.1.campaign_conversion_rate', 6.82)
            ->assertJsonPath('data.scenarios.2.type', 'strong_response')
            ->assertJsonPath('data.scenarios.2.campaign_conversion_rate', 7.82);
    }

    public function test_it_adds_a_custom_scenario_when_a_conversion_rate_is_provided(): void
    {
        $response = $this->withHeader('X-API-Key', 'test-api-key')->postJson('/api/v1/campaigns/simulate/scenarios', [
            'merchant_id' => 202,
            'audience_size' => 800,
            'fixed_campaign_cost' => 100,
            'campaign_type' => 'double_points',
            'campaign_conversion_rate' => 8,
            'parameters' => ['points_multiplier' => 2],
        ]);

        $response->assertOk()
            ->assertJsonCount(4, 'data.scenarios')
            ->assertJsonPath('data.scenarios.3.type', 'custom')
            ->assertJsonPath('data.scenarios.3.campaign_conversion_rate', 8);
    }

    public function test_it_rejects_invalid_campaign_parameters(): void
    {
        $response = $this->withHeader('X-API-Key', 'test-api-key')->postJson('/api/v1/campaigns/simulate', [
            'merchant_id' => 101,
            'audience_size' => 1000,
            'fixed_campaign_cost' => 0,
            'campaign_type' => 'double_points',
            'parameters' => ['points_multiplier' => 0.5],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parameters.points_multiplier']);
    }

    public function test_it_returns_not_found_for_an_unknown_merchant(): void
    {
        $response = $this->withHeader('X-API-Key', 'test-api-key')->postJson('/api/v1/campaigns/simulate', [
            'merchant_id' => 999,
            'audience_size' => 1000,
            'fixed_campaign_cost' => 0,
            'campaign_type' => 'percentage_discount',
            'parameters' => ['discount_percentage' => 10],
        ]);

        $response->assertNotFound();
    }

    public function test_it_rejects_requests_without_an_api_key(): void
    {
        $response = $this->postJson('/api/v1/campaigns/simulate');

        $response->assertUnauthorized();
    }

    public function test_it_rejects_requests_with_an_invalid_api_key(): void
    {
        $response = $this->withHeader('X-API-Key', 'invalid-key')
            ->postJson('/api/v1/campaigns/simulate');

        $response->assertUnauthorized();
    }
}
