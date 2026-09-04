<?php

namespace App\Http\Resources;

use App\Domain\Recommendations\DTOs\ScenarioAdviceDTO;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Same envelope as the plain scenario analysis, with each scenario carrying the parameter
 * changes that would make it viable.
 */
final class CampaignAdviceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $profile = $this->analysis->merchantProfile;

        return [
            'merchant' => [
                'id' => $profile->merchantId,
                'name' => $profile->merchantName,
                'currency' => $profile->currency,
            ],
            'assumptions' => [
                'average_order_value' => $profile->averageOrderValue,
                'gross_margin_percentage' => $profile->grossMarginPercentage,
                'historical_conversion_rate' => $profile->historicalConversionRate,
                'historical_campaign_lift_percentage' => $profile->historicalCampaignLiftPercentage,
            ],
            'fixed_campaign_cost' => $this->analysis->fixedCampaignCost,
            'scenarios' => array_map(
                fn (ScenarioAdviceDTO $advice): array => [
                    'type' => $advice->scenario->scenarioType->value,
                    'campaign_conversion_rate' => $advice->scenario->campaignConversionRate,
                    'result' => (new SimulationResultResource($advice->scenario->result))->toArray($request),
                    'recommendations' => RecommendationSetPresenter::toArray($advice->recommendations),
                ],
                $this->scenarioAdvice,
            ),
        ];
    }
}
