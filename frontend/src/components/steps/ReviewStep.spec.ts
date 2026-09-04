import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import ReviewStep from './ReviewStep.vue';
import type { ScenarioAnalysis } from '../../types/campaign';

const draft = {
  audienceSize: 1200,
  fixedCampaignCost: 250,
  campaignType: 'percentage_discount' as const,
  discountPercentage: 15,
  pointsMultiplier: 2,
};

const scenario: ScenarioAnalysis = {
  type: 'expected',
  campaign_conversion_rate: 6.82,
  recommendations: { target_roi_percentage: -5, already_meets_target: false, summary_message: 'summary', items: [] },
  result: {
    baseline_orders: 36,
    campaign_orders: 48,
    incremental_orders: 12,
    incremental_revenue: 1344,
    incentive_cost: 537.6,
    incremental_contribution: 456.96,
    net_impact: -330.64,
    break_even_conversion_rate: 6.35,
    roi: -41.98,
    health_status: 'risky',
    break_even_achievable: true,
    fixed_campaign_cost: 250,
    average_order_value: 112,
    audience_size: 1200,
    calculation_steps: [],
    insight: {
      break_even_incremental_orders: 18,
      break_even_progress_percentage: 89.9,
      health_driver_message: 'message',
      action_message: 'message',
      orders_context_message: 'message',
    },
  },
};

describe('ReviewStep', () => {
  it('renders the draft summary from the grouped draft prop', () => {
    const wrapper = mount(ReviewStep, {
      props: { draft, currency: 'CAD', campaignName: '15.00% discount campaign', merchant: null, scenario: null },
    });

    expect(wrapper.text()).toContain('1,200');
    expect(wrapper.text()).toContain('percentage_discount');
  });

  it('renders the decision summary from the scenario', () => {
    const wrapper = mount(ReviewStep, {
      props: { draft, currency: 'CAD', campaignName: '15.00% discount campaign', merchant: null, scenario },
    });

    expect(wrapper.text()).toContain('Risky');
    expect(wrapper.text()).toContain('330.64');
  });

  it('emits back and reset from the action row', async () => {
    const wrapper = mount(ReviewStep, {
      props: { draft, currency: 'CAD', campaignName: '15.00% discount campaign', merchant: null, scenario: null },
    });

    const buttons = wrapper.findAll('button');
    await buttons[0]!.trigger('click');
    await buttons[1]!.trigger('click');

    expect(wrapper.emitted('back')).toHaveLength(1);
    expect(wrapper.emitted('reset')).toHaveLength(1);
  });
});





