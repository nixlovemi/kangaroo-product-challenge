<?php

namespace App\Domain\Campaigns\Repositories;

use App\Domain\Campaigns\DTOs\MerchantProfileDTO;

interface HistoricalDataRepository
{
    public function getMerchantProfile(int $merchantId): MerchantProfileDTO;
}
