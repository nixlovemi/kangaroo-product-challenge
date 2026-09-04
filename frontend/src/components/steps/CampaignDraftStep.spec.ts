import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import CampaignDraftStep from './CampaignDraftStep.vue';

const draft = {
  audienceSize: 1200,
  fixedCampaignCost: 250,
  campaignType: 'percentage_discount' as const,
  discountPercentage: 15,
  pointsMultiplier: 2,
};

const baseProps = {
  draft,
  customConversionRate: null,
  loading: false,
  currency: 'CAD',
  campaignName: '15.00% discount campaign',
  summaryResponseRate: '6.82%',
};

describe('CampaignDraftStep', () => {
  it('shows the merchant default conversion rate as a placeholder and hint when no custom rate was entered', () => {
    const wrapper = mount(CampaignDraftStep, {
      props: { ...baseProps, expectedConversionRate: 6.82 },
    });

    const input = wrapper.find('input[placeholder]');
    expect(input.attributes('placeholder')).toBe('Default: 6.82');
    expect(wrapper.text()).toContain("Leave it blank to use this merchant's default estimate of 6.82%");
  });

  it('omits the default estimate hint when the merchant overview has not loaded yet', () => {
    const wrapper = mount(CampaignDraftStep, {
      props: { ...baseProps, expectedConversionRate: null },
    });

    expect(wrapper.text()).not.toContain('default estimate');
  });

  it('emits update:custom when the response field changes', async () => {
    const wrapper = mount(CampaignDraftStep, {
      props: { ...baseProps, expectedConversionRate: 6.82 },
    });

    const inputs = wrapper.findAll('input[type="number"]');
    const responseInput = inputs[inputs.length - 1]!;
    await responseInput.setValue('7.5');

    expect(wrapper.emitted('update:custom')).toEqual([[7.5]]);
  });

  it('emits update:draft with the patched field when the audience size changes', async () => {
    const wrapper = mount(CampaignDraftStep, {
      props: { ...baseProps, expectedConversionRate: 6.82 },
    });

    await wrapper.findAll('input[type="number"]')[0]!.setValue('2000');

    expect(wrapper.emitted('update:draft')).toEqual([[{ ...draft, audienceSize: 2000 }]]);
  });

  it('emits analyze when the primary action is clicked', async () => {
    const wrapper = mount(CampaignDraftStep, {
      props: { ...baseProps, expectedConversionRate: 6.82 },
    });

    await wrapper.findAll('button').find((button) => button.text().includes('Start campaign analysis'))!.trigger('click');

    expect(wrapper.emitted('analyze')).toHaveLength(1);
  });
});



