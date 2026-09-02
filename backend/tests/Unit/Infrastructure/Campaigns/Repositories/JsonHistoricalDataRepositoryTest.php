<?php

namespace Tests\Unit\Infrastructure\Campaigns\Repositories;

use App\Domain\Campaigns\Exceptions\MerchantProfileNotFoundException;
use App\Infrastructure\Campaigns\Repositories\JsonHistoricalDataRepository;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

final class JsonHistoricalDataRepositoryTest extends TestCase
{
    // Keep unit tests deterministic and independent from the application's demo data.
    private const DATA_PATH = __DIR__ . '/../../../../Fixtures/merchant_profiles.json';

    public function test_it_returns_the_requested_merchant_profile_as_a_dto(): void
    {
        $profile = $this->repository()->getMerchantProfile(101);

        self::assertSame(101, $profile->merchantId);
        self::assertSame('Atelier Nord Cafe', $profile->merchantName);
        self::assertSame(68.5, $profile->averageOrderValue);
        self::assertSame(58.0, $profile->grossMarginPercentage);
        self::assertSame(42.0, $profile->historicalCampaignLiftPercentage);
    }

    public function test_it_supports_a_second_merchant_with_a_different_profile(): void
    {
        $profile = $this->repository()->getMerchantProfile(202);

        self::assertSame('Saint-Paul Market', $profile->merchantName);
        self::assertSame(34.0, $profile->grossMarginPercentage);
        self::assertSame(3.1, $profile->historicalConversionRate);
    }

    public function test_it_throws_when_the_merchant_does_not_exist(): void
    {
        $this->expectException(MerchantProfileNotFoundException::class);

        $this->repository()->getMerchantProfile(999);
    }

    private function repository(): JsonHistoricalDataRepository
    {
        return new JsonHistoricalDataRepository(new Filesystem(), self::DATA_PATH);
    }
}
