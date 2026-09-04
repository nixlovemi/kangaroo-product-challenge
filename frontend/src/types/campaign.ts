export type CampaignType = 'percentage_discount' | 'double_points';
export type ScenarioType = 'conservative' | 'expected' | 'strong_response' | 'custom';
export type HealthStatus = 'healthy' | 'caution' | 'risky';

/**
 * The campaign draft fields edited on Step 2, grouped so parent components can
 * pass/update them as one unit instead of listing each field individually.
 */
export interface CampaignDraftSettings {
  audienceSize: number;
  fixedCampaignCost: number;
  campaignType: CampaignType;
  discountPercentage: number;
  pointsMultiplier: number;
}

export interface PercentageDiscountParameters {
  discount_percentage: number;
}

export interface DoublePointsParameters {
  points_multiplier: number;
}

export interface SimulationRequestBody {
  merchant_id: number;
  audience_size: number;
  fixed_campaign_cost: number;
  campaign_type: CampaignType;
  campaign_conversion_rate?: number | null;
  parameters: PercentageDiscountParameters | DoublePointsParameters;
}

export interface MerchantInfo {
  id: number;
  name: string;
  currency: string;
}

export interface MerchantAssumptions {
  average_order_value: number;
  gross_margin_percentage: number;
  historical_conversion_rate: number;
  historical_campaign_lift_percentage: number;
}

export interface SimulationInsight {
  break_even_incremental_orders: number;
  break_even_progress_percentage: number;
  health_driver_message: string;
  action_message: string;
  orders_context_message: string;
}

export type CalculationStepValueType = 'count' | 'currency' | 'percentage';

export interface CalculationStep {
  label: string;
  formula: string;
  value: number;
  value_type: CalculationStepValueType;
}

export interface SimulationMetrics {
  baseline_orders: number;
  campaign_orders: number;
  incremental_orders: number;
  incremental_revenue: number;
  incentive_cost: number;
  incremental_contribution: number;
  net_impact: number;
  break_even_conversion_rate: number;
  roi: number | null;
  health_status: HealthStatus;
  break_even_achievable: boolean;
  fixed_campaign_cost: number;
  average_order_value: number;
  insight: SimulationInsight;
  calculation_steps: CalculationStep[];
}

export interface ScenarioAnalysis {
  type: ScenarioType;
  campaign_conversion_rate: number;
  result: SimulationMetrics;
}

export interface ScenarioAnalysisData {
  merchant: MerchantInfo;
  assumptions: MerchantAssumptions;
  fixed_campaign_cost: number;
  scenarios: ScenarioAnalysis[];
}

export interface MerchantOverviewData {
  merchant: MerchantInfo;
  assumptions: MerchantAssumptions;
  expected_conversion_rate: number;
}

export interface ApiEnvelope<T> {
  success: boolean;
  message: string;
  data: T;
  errors?: Record<string, string[]>;
}
