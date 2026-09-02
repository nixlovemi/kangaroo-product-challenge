<?php

namespace Tests\Unit\Infrastructure\Campaigns\Repositories;

use App\Domain\Campaigns\DTOs\MerchantProfileDTO;
use App\Domain\Campaigns\Repositories\HistoricalDataRepository;
use App\Infrastructure\Campaigns\Repositories\CachedHistoricalDataRepository;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use PHPUnit\Framework\TestCase;

final class CachedHistoricalDataRepositoryTest extends TestCase
{
    public function test_it_reads_the_underlying_repository_only_once_for_the_same_merchant(): void
    {
        $repositoryCalls = 0;
        $profileRepository = new class($repositoryCalls) implements HistoricalDataRepository {
            public function __construct(private int &$repositoryCalls)
            {
            }

            public function getMerchantProfile(int $merchantId): MerchantProfileDTO
            {
                $this->repositoryCalls++;

                return new MerchantProfileDTO($merchantId, 'Cached Merchant', 'CAD', 100, 50, 5, 20, 0.01, 40);
            }
        };
        $cachedRepository = new CachedHistoricalDataRepository(
            $profileRepository,
            new Repository(new ArrayStore()),
            5,
            'v1',
        );

        $firstProfile = $cachedRepository->getMerchantProfile(101);
        $secondProfile = $cachedRepository->getMerchantProfile(101);

        self::assertSame(1, $repositoryCalls);
        self::assertSame($firstProfile->merchantName, $secondProfile->merchantName);
    }

    public function test_a_new_data_version_uses_a_different_cache_entry(): void
    {
        $repositoryCalls = 0;
        $profileRepository = new class($repositoryCalls) implements HistoricalDataRepository {
            public function __construct(private int &$repositoryCalls)
            {
            }

            public function getMerchantProfile(int $merchantId): MerchantProfileDTO
            {
                $this->repositoryCalls++;

                return new MerchantProfileDTO($merchantId, 'Versioned Merchant', 'CAD', 100, 50, 5, 20, 0.01, 40);
            }
        };
        $cache = new Repository(new ArrayStore());
        $firstVersion = new CachedHistoricalDataRepository($profileRepository, $cache, 5, 'v1');
        $secondVersion = new CachedHistoricalDataRepository($profileRepository, $cache, 5, 'v2');

        $firstVersion->getMerchantProfile(101);
        $secondVersion->getMerchantProfile(101);

        self::assertSame(2, $repositoryCalls);
    }
}
