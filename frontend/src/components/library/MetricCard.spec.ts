import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import MetricCard from './MetricCard.vue';

describe('MetricCard', () => {
  it('renders the label, value and optional hint', () => {
    const wrapper = mount(MetricCard, {
      props: { label: 'Incremental orders', value: '12', hint: '36 would order anyway' },
    });

    expect(wrapper.find('span').text()).toBe('Incremental orders');
    expect(wrapper.find('strong').text()).toBe('12');
    expect(wrapper.find('small').text()).toBe('36 would order anyway');
  });

  it('omits the hint element when no hint is provided', () => {
    const wrapper = mount(MetricCard, {
      props: { label: 'ROI', value: '32.91%' },
    });

    expect(wrapper.find('small').exists()).toBe(false);
  });

  it.each([
    ['metric', 'metric-card'],
    ['stat', 'summary-stat'],
  ] as const)('applies the CSS class for variant %s', (variant, expectedClass) => {
    const wrapper = mount(MetricCard, {
      props: { label: 'Label', value: 'Value', variant },
    });

    expect(wrapper.classes()).toContain(expectedClass);
  });

  it('defaults to the metric variant when none is provided', () => {
    const wrapper = mount(MetricCard, {
      props: { label: 'Label', value: 'Value' },
    });

    expect(wrapper.classes()).toContain('metric-card');
  });
});

