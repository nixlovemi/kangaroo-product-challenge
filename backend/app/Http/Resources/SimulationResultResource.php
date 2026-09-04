<?php

namespace App\Http\Resources;

use App\Domain\Campaigns\DTOs\CalculationStepDTO;
use App\Domain\Campaigns\Services\SimulationCalculationTrailBuilderInterface;
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
            'fixed_campaign_cost' => $this->fixedCampaignCost,
            'average_order_value' => $this->averageOrderValue,
            'insight' => [
                'break_even_incremental_orders' => $this->insight->breakEvenIncrementalOrders,
                'break_even_progress_percentage' => $this->insight->breakEvenProgressPercentage,
                'health_driver_message' => $this->insight->healthDriverMessage,
                'action_message' => $this->insight->actionMessage,
                'orders_context_message' => $this->insight->ordersContextMessage,
            ],
            'calculation_steps' => array_map(
                fn (CalculationStepDTO $step): array => [
                    'label' => $step->label,
                    'formula' => $step->formula,
                    'value' => $step->value,
                    'value_type' => $step->valueType,
                ],
                app(SimulationCalculationTrailBuilderInterface::class)->build($this->resource),
            ),
        ];
    }
}
