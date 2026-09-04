<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class MerchantOverviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'merchant' => [
                'id' => $this->merchantProfile->merchantId,
                'name' => $this->merchantProfile->merchantName,
                'currency' => $this->merchantProfile->currency,
            ],
            'assumptions' => [
                'average_order_value' => $this->merchantProfile->averageOrderValue,
                'gross_margin_percentage' => $this->merchantProfile->grossMarginPercentage,
                'historical_conversion_rate' => $this->merchantProfile->historicalConversionRate,
                'historical_campaign_lift_percentage' => $this->merchantProfile->historicalCampaignLiftPercentage,
            ],
            'expected_conversion_rate' => $this->expectedConversionRate,
        ];
    }
}
