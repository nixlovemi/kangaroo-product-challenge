<?php

namespace App\Infrastructure\Campaigns\Repositories;

use App\Domain\Campaigns\DTOs\MerchantProfileDTO;
use App\Domain\Campaigns\Repositories\HistoricalDataRepository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class CachedHistoricalDataRepository implements HistoricalDataRepository
{
    public function __construct(
        private readonly HistoricalDataRepository $repository,
        private readonly CacheRepository $cache,
        private readonly int $ttlMinutes,
        private readonly string $dataVersion,
    ) {
    }

    public function getMerchantProfile(int $merchantId): MerchantProfileDTO
    {
        return $this->cache->remember(
            $this->cacheKey($merchantId),
            now()->addMinutes($this->ttlMinutes),
            fn (): MerchantProfileDTO => $this->repository->getMerchantProfile($merchantId),
        );
    }

    private function cacheKey(int $merchantId): string
    {
        return sprintf('campaigns:merchant-profile:%s:%d', $this->dataVersion, $merchantId);
    }
}
