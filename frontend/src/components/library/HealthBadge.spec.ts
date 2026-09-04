import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import HealthBadge from './HealthBadge.vue';

describe('HealthBadge', () => {
  it.each([
    ['healthy', 'Healthy', 'is-success'],
    ['caution', 'Caution', 'is-warning'],
    ['risky', 'Risky', 'is-danger'],
  ] as const)('renders the %s status with its label and tone class', (status, label, toneClass) => {
    const wrapper = mount(HealthBadge, { props: { status } });

    expect(wrapper.text()).toBe(label);
    expect(wrapper.classes()).toContain(toneClass);
  });

  it('shows a pending state when there is no status yet', () => {
    const wrapper = mount(HealthBadge, { props: { status: null } });

    expect(wrapper.text()).toBe('Pending');
    expect(wrapper.classes()).toContain('is-warning');
  });
});

