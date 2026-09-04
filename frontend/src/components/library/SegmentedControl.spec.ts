import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import SegmentedControl, { type SegmentedOption } from './SegmentedControl.vue';

const scenarioOptions: SegmentedOption[] = [
  { value: 'conservative', label: 'Conservative', sublabel: '5.81%', valueDisplay: '-$653.20' },
  { value: 'expected', label: 'Expected', sublabel: '6.82%', valueDisplay: '-$330.64' },
];

const campaignTypeOptions: SegmentedOption[] = [
  { value: 'percentage_discount', label: 'Percentage discount', sublabel: 'Simple and direct incentive', icon: '%' },
  { value: 'fixed_amount', label: 'Fixed amount', sublabel: 'Coming soon', icon: '$', disabled: true },
];

describe('SegmentedControl', () => {
  it('renders one button per option with its label and sublabel', () => {
    const wrapper = mount(SegmentedControl, {
      props: { options: scenarioOptions, modelValue: 'expected', srLabel: 'Scenarios' },
    });

    const buttons = wrapper.findAll('button');
    expect(buttons).toHaveLength(2);
    expect(buttons[0]!.text()).toContain('Conservative');
    expect(buttons[0]!.text()).toContain('5.81%');
    expect(buttons[0]!.text()).toContain('-$653.20');
  });

  it('marks the option matching modelValue as active', () => {
    const wrapper = mount(SegmentedControl, {
      props: { options: scenarioOptions, modelValue: 'expected', srLabel: 'Scenarios' },
    });

    const buttons = wrapper.findAll('button');
    expect(buttons[0]!.classes()).not.toContain('is-active');
    expect(buttons[1]!.classes()).toContain('is-active');
  });

  it('emits update:modelValue with the clicked option value', async () => {
    const wrapper = mount(SegmentedControl, {
      props: { options: scenarioOptions, modelValue: 'expected', srLabel: 'Scenarios' },
    });

    await wrapper.findAll('button')[0]!.trigger('click');

    expect(wrapper.emitted('update:modelValue')).toEqual([['conservative']]);
  });

  it('renders an icon instead of a trailing value when the option has one', () => {
    const wrapper = mount(SegmentedControl, {
      props: { options: campaignTypeOptions, modelValue: 'percentage_discount', srLabel: 'Campaign type' },
    });

    expect(wrapper.find('.segmented-control__icon').exists()).toBe(true);
    expect(wrapper.find('.segmented-control__value').exists()).toBe(false);
  });

  it('disables options marked as disabled and does not emit on click', async () => {
    const wrapper = mount(SegmentedControl, {
      props: { options: campaignTypeOptions, modelValue: 'percentage_discount', srLabel: 'Campaign type' },
    });

    const disabledButton = wrapper.findAll('button')[1]!;
    expect(disabledButton.attributes('disabled')).toBeDefined();

    await disabledButton.trigger('click');
    expect(wrapper.emitted('update:modelValue')).toBeUndefined();
  });
});

