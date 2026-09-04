import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { useCampaignAdvisor } from './useCampaignAdvisor';

function flushPromises() {
  return new Promise((resolve) => setTimeout(resolve, 0));
}

function mockAnalysisResponse() {
  return {
    success: true,
    message: 'Campaign scenarios calculated.',
    data: {
      merchant: { id: 101, name: 'Atelier Nord Cafe', currency: 'CAD' },
      assumptions: {
        average_order_value: 68.5,
        gross_margin_percentage: 58,
        historical_conversion_rate: 4.8,
        historical_campaign_lift_percentage: 42,
      },
      fixed_campaign_cost: 250,
      scenarios: [
        {
          type: 'conservative',
          campaign_conversion_rate: 5.81,
          result: {
            baseline_orders: 48,
            campaign_orders: 58,
            incremental_orders: 10,
            incremental_revenue: 685,
            incentive_cost: 200,
            incremental_contribution: 397.3,
            net_impact: -102.7,
            break_even_conversion_rate: 6.35,
            roi: -22.8,
            health_status: 'risky',
            break_even_achievable: true,
            insight: {
              break_even_incremental_orders: 15,
              break_even_progress_percentage: 91.5,
              health_driver_message: 'message',
              action_message: 'message',
              orders_context_message: 'message',
            },
          },
        },
        {
          type: 'expected',
          campaign_conversion_rate: 6.82,
          result: {
            baseline_orders: 48,
            campaign_orders: 68,
            incremental_orders: 20,
            incremental_revenue: 1370,
            incentive_cost: 400,
            incremental_contribution: 794.6,
            net_impact: 144.6,
            break_even_conversion_rate: 6.35,
            roi: 36.15,
            health_status: 'healthy',
            break_even_achievable: true,
            insight: {
              break_even_incremental_orders: 15,
              break_even_progress_percentage: 107.4,
              health_driver_message: 'message',
              action_message: 'message',
              orders_context_message: 'message',
            },
          },
        },
      ],
    },
  };
}

function mockOverviewResponse(overrides: Partial<{ id: number; name: string; expectedConversionRate: number }> = {}) {
  return {
    success: true,
    message: 'Merchant profile retrieved.',
    data: {
      merchant: { id: overrides.id ?? 101, name: overrides.name ?? 'Atelier Nord Cafe', currency: 'CAD' },
      assumptions: {
        average_order_value: 68.5,
        gross_margin_percentage: 58,
        historical_conversion_rate: 4.8,
        historical_campaign_lift_percentage: 42,
      },
      expected_conversion_rate: overrides.expectedConversionRate ?? 6.82,
    },
  };
}

describe('useCampaignAdvisor', () => {
  beforeEach(() => {
    vi.stubGlobal('fetch', vi.fn().mockImplementation((url: string) => {
      const isProfileRequest = url.includes('/profile');

      return Promise.resolve({
        ok: true,
        json: () => Promise.resolve(isProfileRequest ? mockOverviewResponse() : mockAnalysisResponse()),
      });
    }));
  });

  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('starts on the merchant step with sensible defaults', () => {
    const advisor = useCampaignAdvisor();

    expect(advisor.state.step).toBe('merchant');
    expect(advisor.state.analysis).toBeNull();
    expect(advisor.selectedScenario.value).toBe('expected');
  });

  it('loads the default merchant overview (expected conversion rate) as soon as the advisor is created', async () => {
    const advisor = useCampaignAdvisor();

    await flushPromises();

    expect(advisor.state.merchantOverview?.expected_conversion_rate).toBe(6.82);
  });

  it('selectMerchant updates the merchant id and reloads its overview', async () => {
    const advisor = useCampaignAdvisor();
    await flushPromises();

    vi.mocked(fetch).mockImplementation(() => Promise.resolve({
      ok: true,
      json: () => Promise.resolve(mockOverviewResponse({ id: 202, name: 'Saint-Paul Market', expectedConversionRate: 3.66 })),
    } as Response));

    advisor.selectMerchant(202);
    await flushPromises();

    expect(advisor.state.merchantId).toBe(202);
    expect(advisor.state.merchantOverview?.merchant.name).toBe('Saint-Paul Market');
    expect(advisor.state.merchantOverview?.expected_conversion_rate).toBe(3.66);
  });

  it('clears the previous merchant overview immediately so a stale rate never flashes on screen', async () => {
    const advisor = useCampaignAdvisor();
    await flushPromises();
    expect(advisor.state.merchantOverview?.expected_conversion_rate).toBe(6.82);

    vi.mocked(fetch).mockImplementation(() => Promise.resolve({
      ok: true,
      json: () => Promise.resolve(mockOverviewResponse({ id: 202, expectedConversionRate: 3.66 })),
    } as Response));

    advisor.selectMerchant(202);

    expect(advisor.state.merchantOverview).toBeNull();

    await flushPromises();
    expect(advisor.state.merchantOverview?.expected_conversion_rate).toBe(3.66);
  });

  it('moves to the analysis step and stores the response after a successful analyze()', async () => {
    const advisor = useCampaignAdvisor();

    await advisor.analyze();

    expect(advisor.state.step).toBe('analysis');
    expect(advisor.state.loading).toBe(false);
    expect(advisor.state.error).toBeNull();
    expect(advisor.merchant.value?.name).toBe('Atelier Nord Cafe');
    expect(advisor.scenarios.value).toHaveLength(2);
  });

  it('defaults currentScenario to the expected scenario', async () => {
    const advisor = useCampaignAdvisor();

    await advisor.analyze();

    expect(advisor.currentScenario.value?.type).toBe('expected');
  });

  it('selectScenario switches the currently active scenario', async () => {
    const advisor = useCampaignAdvisor();
    await advisor.analyze();

    advisor.selectScenario('conservative');

    expect(advisor.selectedScenario.value).toBe('conservative');
    expect(advisor.currentScenario.value?.type).toBe('conservative');
  });

  it('calls the scenarios endpoint exactly once per analyze(), even with a custom conversion rate set', async () => {
    const advisor = useCampaignAdvisor();
    await flushPromises();
    vi.mocked(fetch).mockClear();

    advisor.setCustomConversionRate(6.82);
    await advisor.analyze();

    const scenarioCalls = vi.mocked(fetch).mock.calls.filter(([url]) => String(url).includes('/scenarios'));
    expect(scenarioCalls).toHaveLength(1);
    expect(advisor.selectedScenario.value).toBe('custom');
  });

  it('selects the custom scenario after analyzing when a Step 2 response rate was entered', async () => {
    const advisor = useCampaignAdvisor();
    advisor.selectScenario('conservative');

    advisor.setCustomConversionRate(6.82);
    await advisor.analyze();

    expect(advisor.selectedScenario.value).toBe('custom');
  });

  it('selects the expected scenario after analyzing when the Step 2 response rate is left empty', async () => {
    const advisor = useCampaignAdvisor();
    advisor.selectScenario('conservative');

    advisor.setCustomConversionRate(null);
    await advisor.analyze();

    expect(advisor.selectedScenario.value).toBe('expected');
  });

  it('rounds a custom conversion rate to two decimal places', () => {
    const advisor = useCampaignAdvisor();

    advisor.setCustomConversionRate(6.8261);

    expect(advisor.state.customConversionRate).toBe(6.83);
  });

  it('clears the custom conversion rate when given null', () => {
    const advisor = useCampaignAdvisor();

    advisor.setCustomConversionRate(6.5);
    advisor.setCustomConversionRate(null);

    expect(advisor.state.customConversionRate).toBeNull();
  });

  it('sets an error and clears the analysis when the API call fails', async () => {
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue({
      ok: false,
      json: () => Promise.resolve({ success: false, message: 'The simulation request is invalid.', data: null, errors: {} }),
    }));

    const advisor = useCampaignAdvisor();
    await advisor.analyze();

    expect(advisor.state.error).toBe('The simulation request is invalid.');
    expect(advisor.state.analysis).toBeNull();
    expect(advisor.state.loading).toBe(false);
  });

  it('reset() returns to the merchant step and clears the analysis', async () => {
    const advisor = useCampaignAdvisor();
    await advisor.analyze();

    advisor.reset();

    expect(advisor.state.step).toBe('merchant');
    expect(advisor.state.analysis).toBeNull();
    expect(advisor.selectedScenario.value).toBe('expected');
  });
});
