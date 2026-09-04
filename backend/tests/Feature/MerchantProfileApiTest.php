<?php

namespace Tests\Feature;

use Tests\TestCase;

final class MerchantProfileApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['api.key' => 'test-api-key']);
    }

    public function test_it_returns_the_merchant_profile_and_expected_conversion_rate(): void
    {
        $response = $this->withHeader('X-API-Key', 'test-api-key')->getJson('/api/v1/merchants/101/profile');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Merchant profile retrieved.')
            ->assertJsonPath('data.merchant.id', 101)
            ->assertJsonPath('data.merchant.name', 'Atelier Nord Cafe')
            ->assertJsonPath('data.assumptions.historical_conversion_rate', 4.8)
            ->assertJsonPath('data.assumptions.historical_campaign_lift_percentage', 42)
            ->assertJsonPath('data.expected_conversion_rate', 6.82);
    }

    public function test_it_returns_a_404_for_an_unknown_merchant(): void
    {
        $response = $this->withHeader('X-API-Key', 'test-api-key')->getJson('/api/v1/merchants/999/profile');

        $response->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null);
    }

    public function test_it_rejects_requests_without_a_valid_api_key(): void
    {
        $response = $this->withHeader('X-API-Key', 'invalid-key')->getJson('/api/v1/merchants/101/profile');

        $response->assertUnauthorized();
    }
}
