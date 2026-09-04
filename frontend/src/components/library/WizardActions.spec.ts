import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import WizardActions from './WizardActions.vue';

describe('WizardActions', () => {
  it('renders default back label and the given primary label', () => {
    const wrapper = mount(WizardActions, { props: { primaryLabel: 'Continue to review' } });

    expect(wrapper.text()).toContain('Back');
    expect(wrapper.text()).toContain('Continue to review');
  });

  it('emits back and primary events on click', async () => {
    const wrapper = mount(WizardActions, { props: { primaryLabel: 'Continue' } });
    const buttons = wrapper.findAll('button');

    await buttons[0]!.trigger('click');
    await buttons[1]!.trigger('click');

    expect(wrapper.emitted('back')).toHaveLength(1);
    expect(wrapper.emitted('primary')).toHaveLength(1);
  });

  it('disables the primary button when primaryDisabled is true', () => {
    const wrapper = mount(WizardActions, { props: { primaryLabel: 'Start', primaryDisabled: true } });

    expect(wrapper.findAll('button')[1]!.attributes('disabled')).toBeDefined();
  });

  it('renders the primary button as secondary-styled when primaryVariant is secondary', () => {
    const wrapper = mount(WizardActions, { props: { primaryLabel: 'Start over', primaryVariant: 'secondary' } });

    expect(wrapper.findAll('button')[1]!.classes()).toContain('secondary-button');
  });
});

