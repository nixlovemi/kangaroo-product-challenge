import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import RecommendationsAccordion from './RecommendationsAccordion.vue';
import type { Recommendation, RecommendationSet, ScenarioAnalysis } from '../../types/campaign';

function buildScenario(recommendations: RecommendationSet): ScenarioAnalysis {
  return {
    type: 'expected',
    campaign_conversion_rate: 6.82,
    recommendations,
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
        health_driver_message: 'driver',
        action_message: 'action',
        orders_context_message: 'orders',
      },
    },
  };
}

const actionable: Recommendation = {
  lever: 'discount_percentage',
  label: 'Discount percentage',
  value_type: 'percentage',
  outcome: 'actionable',
  message: 'Cut the discount from 15.00% to 8.00% to lift ROI to -4.09%.',
  current_value: 15,
  suggested_value: 8,
  projected_roi: -4.09,
};

const diagnosis: Recommendation = {
  lever: 'fixed_campaign_cost',
  label: 'Fixed campaign cost',
  value_type: 'currency',
  outcome: 'infeasible',
  message: 'Cutting the fixed cost will not rescue this campaign.',
  current_value: 250,
  suggested_value: null,
  projected_roi: -47.98,
};

function buildSet(items: Recommendation[], overrides: Partial<RecommendationSet> = {}): RecommendationSet {
  return {
    target_roi_percentage: -5,
    already_meets_target: false,
    summary_message: 'Any one of these changes would bring this campaign to the -5.00% ROI target.',
    items,
    ...overrides,
  };
}

describe('RecommendationsAccordion', () => {
  it('renders nothing when the scenario has no recommendations', () => {
    const wrapper = mount(RecommendationsAccordion, { props: { scenario: null, currency: 'CAD' } });

    expect(wrapper.find('details').exists()).toBe(false);
  });

  it('shows the current to suggested change for an actionable recommendation', () => {
    const wrapper = mount(RecommendationsAccordion, {
      props: { scenario: buildScenario(buildSet([actionable])), currency: 'CAD' },
    });

    const row = wrapper.find('.recommendation');
    expect(row.classes()).toContain('is-actionable');
    expect(row.text()).toContain('15.00%');
    expect(row.text()).toContain('8.00%');
  });

  it('marks a diagnosis row as having no viable change', () => {
    const wrapper = mount(RecommendationsAccordion, {
      props: { scenario: buildScenario(buildSet([diagnosis])), currency: 'CAD' },
    });

    const row = wrapper.find('.recommendation');
    expect(row.classes()).toContain('is-infeasible');
    expect(row.text()).toContain('no viable change');
  });

  it('counts actionable changes in the headline', () => {
    const wrapper = mount(RecommendationsAccordion, {
      props: { scenario: buildScenario(buildSet([actionable, diagnosis])), currency: 'CAD' },
    });

    expect(wrapper.find('h3').text()).toBe('1 change would make this work');
  });

  it('headlines the diagnosis when nothing is actionable', () => {
    const wrapper = mount(RecommendationsAccordion, {
      props: { scenario: buildScenario(buildSet([diagnosis])), currency: 'CAD' },
    });

    expect(wrapper.find('h3').text()).toBe('Why no tweak fixes this');
  });

  it('says no changes are needed when the campaign already meets the target', () => {
    const wrapper = mount(RecommendationsAccordion, {
      props: {
        scenario: buildScenario(buildSet([], { already_meets_target: true, summary_message: 'Already clears the target.' })),
        currency: 'CAD',
      },
    });

    expect(wrapper.find('h3').text()).toBe('No changes needed');
    expect(wrapper.text()).toContain('Already clears the target.');
  });
});
