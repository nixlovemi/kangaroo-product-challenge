<?php

namespace App\Domain\Campaigns\DTOs;

use App\Domain\Campaigns\Enums\CampaignType;

final class CampaignDraftDTO
{
    public function __construct(
        public readonly int $merchantId,
        public readonly int $audienceSize,
        public readonly float $fixedCampaignCost,
        public readonly CampaignParameters $parameters,
        public readonly CampaignType $campaignType = CampaignType::PERCENTAGE_DISCOUNT,
        public readonly ?float $campaignConversionRate = null,
    ) {
    }
}
