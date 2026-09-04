import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import ScenarioSelector from './ScenarioSelector.vue';
import type { ScenarioAnalysis } from '../../types/campaign';

function buildScenario(type: ScenarioAnalysis['type'], rate: number, netImpact: number): ScenarioAnalysis {
  return {
    type,
    campaign_conversion_rate: rate,
    recommendations: { target_roi_percentage: -5, already_meets_target: false, summary_message: 'summary', items: [] },
    result: {
      baseline_orders: 36,
      campaign_orders: 48,
      incremental_orders: 12,
      incremental_revenue: 1344,
      incentive_cost: 537.6,
      incremental_contribution: 456.96,
      net_impact: netImpact,
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
}

const scenarios = [
  buildScenario('conservative', 5.81, -653.2),
  buildScenario('expected', 6.82, -330.64),
];

describe('ScenarioSelector', () => {
  it('renders every preset scenario plus the custom option', () => {
    const wrapper = mount(ScenarioSelector, {
      props: { scenarios, selectedScenario: 'expected', customScenario: null, customConversionRate: null, currency: 'CAD' },
    });

    expect(wrapper.text()).toContain('Conservative');
    expect(wrapper.text()).toContain('Expected');
    expect(wrapper.text()).toContain('Custom');
  });

  it('emits select-scenario when a preset button is clicked', async () => {
    const wrapper = mount(ScenarioSelector, {
      props: { scenarios, selectedScenario: 'expected', customScenario: null, customConversionRate: null, currency: 'CAD' },
    });

    await wrapper.findAll('.segmented-control__option')[0]!.trigger('click');

    expect(wrapper.emitted('select-scenario')).toEqual([['conservative']]);
  });

  it('opens the custom modal and selects the custom scenario when the Custom button is clicked', async () => {
    const wrapper = mount(ScenarioSelector, {
      props: { scenarios, selectedScenario: 'expected', customScenario: null, customConversionRate: null, currency: 'CAD' },
    });

    expect(wrapper.find('.custom-dialog').exists()).toBe(false);

    await wrapper.find('.scenario-option--custom').trigger('click');

    expect(wrapper.find('.custom-dialog').exists()).toBe(true);
    expect(wrapper.emitted('select-scenario')).toEqual([['custom']]);
  });

  it('closes the custom modal when Cancel is clicked and reverts selection to expected', async () => {
    const wrapper = mount(ScenarioSelector, {
      props: { scenarios, selectedScenario: 'expected', customScenario: null, customConversionRate: null, currency: 'CAD' },
    });

    await wrapper.find('.scenario-option--custom').trigger('click');
    await wrapper.find('.custom-dialog__actions .secondary-button').trigger('click');

    expect(wrapper.find('.custom-dialog').exists()).toBe(false);
    expect(wrapper.emitted('select-scenario')!.at(-1)).toEqual(['expected']);
  });

  it('emits update:custom when typing a custom conversion rate', async () => {
    const wrapper = mount(ScenarioSelector, {
      props: { scenarios, selectedScenario: 'custom', customScenario: null, customConversionRate: null, currency: 'CAD' },
    });

    await wrapper.find('.scenario-option--custom').trigger('click');
    await wrapper.find('.custom-dialog__input').setValue('7.5');

    expect(wrapper.emitted('update:custom')).toEqual([[7.5]]);
  });
});





