<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class CampaignScenarioAnalysisResource extends JsonResource
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
            'fixed_campaign_cost' => $this->fixedCampaignCost,
            'scenarios' => array_map(
                fn ($scenario): array => [
                    'type' => $scenario->scenarioType->value,
                    'campaign_conversion_rate' => $scenario->campaignConversionRate,
                    'result' => (new SimulationResultResource($scenario->result))->toArray($request),
                ],
                $this->scenarios,
            ),
        ];
    }
}
