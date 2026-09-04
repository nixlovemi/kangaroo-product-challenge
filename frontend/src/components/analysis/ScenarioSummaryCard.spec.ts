import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import ScenarioSummaryCard from './ScenarioSummaryCard.vue';
import type { ScenarioAnalysis } from '../../types/campaign';

function buildScenario(overrides: Partial<ScenarioAnalysis['result']> = {}): ScenarioAnalysis {
  return {
    type: 'expected',
    campaign_conversion_rate: 6.82,
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
      calculation_steps: [],
      insight: {
        break_even_incremental_orders: 18,
        break_even_progress_percentage: 89.9,
        health_driver_message: 'Risky because the projected conversion (6.82%) is below the 6.35% needed to cover incentive and campaign costs.',
        action_message: 'Needs 6 more incremental orders (+0.53pp conversion) to break even.',
        orders_context_message: '36 would order anyway · 48 total with this campaign',
      },
      ...overrides,
    },
  };
}

describe('ScenarioSummaryCard', () => {
  it('shows the placeholder state when there is no scenario yet', () => {
    const wrapper = mount(ScenarioSummaryCard, { props: { scenario: null, currency: 'CAD' } });

    expect(wrapper.find('.summary-card').exists()).toBe(false);
  });

  it('renders the net impact, driver message and action message from the backend insight', () => {
    const wrapper = mount(ScenarioSummaryCard, { props: { scenario: buildScenario(), currency: 'CAD' } });

    expect(wrapper.text()).toContain('$330.64');
    expect(wrapper.text()).toContain('Risky because the projected conversion');
    expect(wrapper.text()).toContain('Needs 6 more incremental orders');
    expect(wrapper.text()).toContain('36 would order anyway');
  });

  it('shows the "Adjust campaign parameters" CTA when the scenario is not healthy', () => {
    const wrapper = mount(ScenarioSummaryCard, { props: { scenario: buildScenario(), currency: 'CAD' } });

    expect(wrapper.find('.summary-action__cta').exists()).toBe(true);
  });

  it('hides the CTA when the scenario is healthy', () => {
    const wrapper = mount(ScenarioSummaryCard, {
      props: { scenario: buildScenario({ health_status: 'healthy' }), currency: 'CAD' },
    });

    expect(wrapper.find('.summary-action__cta').exists()).toBe(false);
  });

  it('emits adjust when the CTA is clicked', async () => {
    const wrapper = mount(ScenarioSummaryCard, { props: { scenario: buildScenario(), currency: 'CAD' } });

    await wrapper.find('.summary-action__cta').trigger('click');

    expect(wrapper.emitted('adjust')).toHaveLength(1);
  });
});

