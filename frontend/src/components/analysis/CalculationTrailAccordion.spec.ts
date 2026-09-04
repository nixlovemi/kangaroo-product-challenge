import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import CalculationTrailAccordion from './CalculationTrailAccordion.vue';
import type { ScenarioAnalysis } from '../../types/campaign';

function buildScenario(calculationSteps: ScenarioAnalysis['result']['calculation_steps']): ScenarioAnalysis {
  return {
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
      calculation_steps: calculationSteps,
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

const steps = [
  { label: 'Incremental orders', formula: '48.00 campaign orders − 36.00 baseline orders', value: 12, value_type: 'count' as const },
  { label: 'Net impact', formula: '456.96 contribution − 537.60 incentive cost − 250.00 fixed cost', value: -330.64, value_type: 'currency' as const },
  { label: 'Break-even conversion', formula: 'Conversion needed for zero net impact.', value: 6.35, value_type: 'percentage' as const },
];

describe('CalculationTrailAccordion', () => {
  it('renders nothing when the scenario has no calculation steps', () => {
    const wrapper = mount(CalculationTrailAccordion, { props: { scenario: null, currency: 'CAD' } });

    expect(wrapper.find('details').exists()).toBe(false);
  });

  it('renders one row per calculation step, with its formula', () => {
    const wrapper = mount(CalculationTrailAccordion, {
      props: { scenario: buildScenario(steps), currency: 'CAD' },
    });

    const rows = wrapper.findAll('.trail__step');
    expect(rows).toHaveLength(3);
    expect(rows[0]!.text()).toContain('Incremental orders');
    expect(rows[0]!.text()).toContain('48.00 campaign orders − 36.00 baseline orders');
  });

  it('formats each value according to its declared type', () => {
    const wrapper = mount(CalculationTrailAccordion, {
      props: { scenario: buildScenario(steps), currency: 'CAD' },
    });

    const values = wrapper.findAll('.trail__value').map((node) => node.text());

    expect(values[0]).toBe('12');
    expect(values[1]).toContain('330.64');
    expect(values[2]).toBe('6.35%');
  });

  it('highlights the net impact row as the outcome of the trail', () => {
    const wrapper = mount(CalculationTrailAccordion, {
      props: { scenario: buildScenario(steps), currency: 'CAD' },
    });

    const rows = wrapper.findAll('.trail__step');

    expect(rows[0]!.classes()).not.toContain('is-outcome');
    expect(rows[1]!.classes()).toContain('is-outcome');
  });
});




