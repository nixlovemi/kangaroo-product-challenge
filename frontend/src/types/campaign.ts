export type CampaignType = 'percentage_discount' | 'double_points';
export type ScenarioType = 'conservative' | 'expected' | 'strong_response' | 'custom';
export type HealthStatus = 'healthy' | 'caution' | 'risky';

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

export interface ApiEnvelope<T> {
  success: boolean;
  message: string;
  data: T;
  errors?: Record<string, string[]>;
}
