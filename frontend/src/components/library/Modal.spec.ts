import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import Modal from './Modal.vue';

describe('Modal', () => {
  it('renders the eyebrow and title', () => {
    const wrapper = mount(Modal, {
      props: { eyebrow: 'Custom scenario', title: 'Test a different audience response', srLabel: 'Custom scenario' },
    });

    expect(wrapper.text()).toContain('Custom scenario');
    expect(wrapper.text()).toContain('Test a different audience response');
  });

  it('renders default and actions slot content', () => {
    const wrapper = mount(Modal, {
      props: { title: 'Title', srLabel: 'Title' },
      slots: {
        default: '<p class="body-slot">Body content</p>',
        actions: '<button class="action-slot">Apply</button>',
      },
    });

    expect(wrapper.find('.body-slot').exists()).toBe(true);
    expect(wrapper.find('.action-slot').exists()).toBe(true);
  });

  it('emits close when the close button is clicked', async () => {
    const wrapper = mount(Modal, { props: { title: 'Title', srLabel: 'Title' } });

    await wrapper.find('.custom-dialog__close').trigger('click');

    expect(wrapper.emitted('close')).toHaveLength(1);
  });

  it('emits close when the backdrop itself is clicked', async () => {
    const wrapper = mount(Modal, { props: { title: 'Title', srLabel: 'Title' } });

    await wrapper.find('.custom-dialog-backdrop').trigger('click');

    expect(wrapper.emitted('close')).toHaveLength(1);
  });

  it('does not emit close when clicking inside the dialog panel', async () => {
    const wrapper = mount(Modal, { props: { title: 'Title', srLabel: 'Title' } });

    await wrapper.find('.custom-dialog').trigger('click');

    expect(wrapper.emitted('close')).toBeUndefined();
  });
});

