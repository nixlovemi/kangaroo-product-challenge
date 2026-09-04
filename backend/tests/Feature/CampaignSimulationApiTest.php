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
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Campaign simulation completed.')
            ->assertJsonPath('data.health_status', 'caution')
            ->assertJsonPath('data.baseline_orders', 48)
            ->assertJsonMissingPath('data.data')
            ->assertJsonMissingPath('data.discount_cost')
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
                'insight' => [
                    'break_even_incremental_orders',
                    'break_even_progress_percentage',
                    'health_driver_message',
                    'action_message',
                    'orders_context_message',
                ],
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
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Campaign scenarios calculated.')
            ->assertJsonPath('data.merchant.id', 101)
            ->assertJsonPath('data.assumptions.historical_campaign_lift_percentage', 42)
            ->assertJsonPath('data.fixed_campaign_cost', 0)
            ->assertJsonCount(3, 'data.scenarios')
            ->assertJsonPath('data.scenarios.0.type', 'conservative')
            ->assertJsonPath('data.scenarios.0.campaign_conversion_rate', 5.81)
            ->assertJsonPath('data.scenarios.1.type', 'expected')
            ->assertJsonPath('data.scenarios.1.campaign_conversion_rate', 6.82)
            ->assertJsonPath('data.scenarios.2.type', 'strong_response')
            ->assertJsonPath('data.scenarios.2.campaign_conversion_rate', 7.82)
            ->assertJsonStructure(['data' => ['scenarios' => [['result' => [
                'insight' => [
                    'break_even_incremental_orders',
                    'break_even_progress_percentage',
                    'health_driver_message',
                    'action_message',
                    'orders_context_message',
                ],
            ]]]]]);
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
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null)
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

        $response->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null);
    }

    public function test_it_rejects_requests_without_an_api_key(): void
    {
        $response = $this->postJson('/api/v1/campaigns/simulate');

        $response->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null);
    }

    public function test_it_rejects_requests_with_an_invalid_api_key(): void
    {
        $response = $this->withHeader('X-API-Key', 'invalid-key')
            ->postJson('/api/v1/campaigns/simulate');

        $response->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null);
    }

    public function test_it_returns_the_standard_error_envelope_for_a_missing_required_payload(): void
    {
        $response = $this->authorizedPost('/api/v1/campaigns/simulate', []);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'The simulation request is invalid.')
            ->assertJsonPath('data', null)
            ->assertJsonStructure(['errors' => [
                'merchant_id',
                'audience_size',
                'fixed_campaign_cost',
                'campaign_type',
                'parameters',
            ]]);
    }

    public function test_it_rejects_an_audience_outside_the_supported_range(): void
    {
        foreach ([0, 1000001] as $audienceSize) {
            $response = $this->authorizedPost('/api/v1/campaigns/simulate', [
                ...$this->validPercentagePayload(),
                'audience_size' => $audienceSize,
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['audience_size']);
        }
    }

    public function test_it_rejects_a_negative_or_excessive_fixed_campaign_cost(): void
    {
        foreach ([-0.01, 1000000] as $fixedCampaignCost) {
            $response = $this->authorizedPost('/api/v1/campaigns/simulate', [
                ...$this->validPercentagePayload(),
                'fixed_campaign_cost' => $fixedCampaignCost,
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['fixed_campaign_cost']);
        }
    }

    public function test_it_rejects_an_unknown_campaign_type(): void
    {
        $response = $this->authorizedPost('/api/v1/campaigns/simulate', [
            ...$this->validPercentagePayload(),
            'campaign_type' => 'buy_one_get_one',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['campaign_type']);
    }

    public function test_it_requires_discount_parameters_for_percentage_campaigns(): void
    {
        $response = $this->authorizedPost('/api/v1/campaigns/simulate', [
            ...$this->validPercentagePayload(),
            'parameters' => [],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['parameters.discount_percentage']);
    }

    public function test_it_rejects_discount_percentages_outside_the_supported_range(): void
    {
        foreach ([-0.01, 100.01] as $discountPercentage) {
            $response = $this->authorizedPost('/api/v1/campaigns/simulate', [
                ...$this->validPercentagePayload(),
                'parameters' => ['discount_percentage' => $discountPercentage],
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['parameters.discount_percentage']);
        }
    }

    public function test_it_requires_points_parameters_for_double_points_campaigns(): void
    {
        $response = $this->authorizedPost('/api/v1/campaigns/simulate', [
            ...$this->validPercentagePayload(),
            'campaign_type' => 'double_points',
            'parameters' => [],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['parameters.points_multiplier']);
    }

    public function test_it_rejects_a_points_multiplier_outside_the_supported_range(): void
    {
        foreach ([0.99, 10.01] as $pointsMultiplier) {
            $response = $this->authorizedPost('/api/v1/campaigns/simulate', [
                ...$this->validPercentagePayload(),
                'campaign_type' => 'double_points',
                'parameters' => ['points_multiplier' => $pointsMultiplier],
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['parameters.points_multiplier']);
        }
    }

    public function test_it_accepts_zero_and_one_hundred_percent_conversion_rates(): void
    {
        foreach ([0, 100] as $campaignConversionRate) {
            $response = $this->authorizedPost('/api/v1/campaigns/simulate', [
                ...$this->validPercentagePayload(),
                'campaign_conversion_rate' => $campaignConversionRate,
            ]);

            $response->assertOk()
                ->assertJsonPath('success', true);
        }
    }

    public function test_it_includes_a_custom_scenario_only_when_a_conversion_rate_is_provided(): void
    {
        $withoutCustom = $this->authorizedPost(
            '/api/v1/campaigns/simulate/scenarios',
            $this->validPercentagePayload(),
        );
        $withCustom = $this->authorizedPost('/api/v1/campaigns/simulate/scenarios', [
            ...$this->validPercentagePayload(),
            'campaign_conversion_rate' => 7.5,
        ]);

        $withoutCustom->assertOk()->assertJsonCount(3, 'data.scenarios');
        $withCustom->assertOk()
            ->assertJsonCount(4, 'data.scenarios')
            ->assertJsonPath('data.scenarios.3.type', 'custom')
            ->assertJsonPath('data.scenarios.3.campaign_conversion_rate', 7.5);
    }

    public function test_it_rejects_requests_when_the_configured_api_key_is_empty(): void
    {
        config(['api.key' => '']);

        $response = $this->withHeader('X-API-Key', 'test-api-key')
            ->postJson('/api/v1/campaigns/simulate', $this->validPercentagePayload());

        $response->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null);
    }

    public function test_it_rejects_invalid_scalar_formats(): void
    {
        foreach ([
            'merchant_id' => 'not-an-integer',
            'audience_size' => 'not-an-integer',
            'fixed_campaign_cost' => 'not-a-number',
            'campaign_conversion_rate' => 'not-a-number',
        ] as $field => $value) {
            $response = $this->authorizedPost('/api/v1/campaigns/simulate', [
                ...$this->validPercentagePayload(),
                $field => $value,
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors([$field]);
        }
    }

    public function test_it_rejects_campaign_conversion_rates_outside_the_supported_range(): void
    {
        foreach ([-0.01, 100.01] as $campaignConversionRate) {
            $response = $this->authorizedPost('/api/v1/campaigns/simulate', [
                ...$this->validPercentagePayload(),
                'campaign_conversion_rate' => $campaignConversionRate,
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['campaign_conversion_rate']);
        }
    }

    public function test_it_requires_parameters_to_be_an_array(): void
    {
        $response = $this->authorizedPost('/api/v1/campaigns/simulate', [
            ...$this->validPercentagePayload(),
            'parameters' => 'invalid-parameters',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['parameters']);
    }

    public function test_it_rejects_a_non_numeric_parameter_value(): void
    {
        $response = $this->authorizedPost('/api/v1/campaigns/simulate', [
            ...$this->validPercentagePayload(),
            'parameters' => ['discount_percentage' => 'not-a-number'],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['parameters.discount_percentage']);
    }

    public function test_it_applies_the_same_validation_contract_to_scenario_analysis(): void
    {
        $response = $this->authorizedPost('/api/v1/campaigns/simulate/scenarios', [
            ...$this->validPercentagePayload(),
            'parameters' => ['discount_percentage' => 100.01],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['parameters.discount_percentage'])
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null);
    }

    private function validPercentagePayload(): array
    {
        return [
            'merchant_id' => 101,
            'audience_size' => 1000,
            'fixed_campaign_cost' => 0,
            'campaign_type' => 'percentage_discount',
            'parameters' => ['discount_percentage' => 10],
        ];
    }

    private function authorizedPost(string $uri, array $payload)
    {
        return $this->withHeader('X-API-Key', 'test-api-key')->postJson($uri, $payload);
    }
}
