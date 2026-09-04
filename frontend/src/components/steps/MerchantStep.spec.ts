import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import MerchantStep from './MerchantStep.vue';

describe('MerchantStep', () => {
  it('emits choose-merchant with 101 when the first merchant card is clicked', async () => {
    const wrapper = mount(MerchantStep);

    await wrapper.findAll('.choice-card')[0]!.trigger('click');

    expect(wrapper.emitted('choose-merchant')).toEqual([[101]]);
  });

  it('emits choose-merchant with 202 when the second merchant card is clicked', async () => {
    const wrapper = mount(MerchantStep);

    await wrapper.findAll('.choice-card')[1]!.trigger('click');

    expect(wrapper.emitted('choose-merchant')).toEqual([[202]]);
  });
});

