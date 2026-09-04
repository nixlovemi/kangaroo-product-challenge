<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class SimulationResultResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'baseline_orders' => $this->baselineOrders,
            'campaign_orders' => $this->campaignOrders,
            'incremental_orders' => $this->incrementalOrders,
            'incremental_revenue' => $this->incrementalRevenue,
            'incentive_cost' => $this->incentiveCost,
            'incremental_contribution' => $this->incrementalContribution,
            'net_impact' => $this->netImpact,
            'break_even_conversion_rate' => $this->breakEvenConversionRate,
            'roi' => $this->roi,
            'health_status' => $this->healthStatus->value,
            'break_even_achievable' => $this->breakEvenAchievable,
            'insight' => [
                'break_even_incremental_orders' => $this->insight->breakEvenIncrementalOrders,
                'break_even_progress_percentage' => $this->insight->breakEvenProgressPercentage,
                'health_driver_message' => $this->insight->healthDriverMessage,
                'action_message' => $this->insight->actionMessage,
                'orders_context_message' => $this->insight->ordersContextMessage,
            ],
        ];
    }
}
