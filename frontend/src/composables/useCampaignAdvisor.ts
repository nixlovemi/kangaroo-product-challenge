import { computed, reactive, ref } from 'vue';
import { CampaignSimulationClient } from '../api/campaignSimulationClient';
import type { CampaignType, ScenarioAnalysisData, ScenarioType } from '../types/campaign';

type WizardStep = 'merchant' | 'campaign' | 'analysis' | 'review';

const client = new CampaignSimulationClient();

export function useCampaignAdvisor() {
  const state = reactive({
    step: 'merchant' as WizardStep,
    merchantId: 101,
    audienceSize: 1200,
    fixedCampaignCost: 250,
    campaignType: 'percentage_discount' as CampaignType,
    discountPercentage: 15,
    pointsMultiplier: 2,
    customConversionRate: null as number | null,
    analysis: null as ScenarioAnalysisData | null,
    loading: false,
    error: null as string | null,
  });

  const selectedScenario = ref<ScenarioType>('expected');

  const scenarios = computed(() => state.analysis?.scenarios ?? []);
  const currentScenario = computed(() =>
    scenarios.value.find((scenario) => scenario.type === selectedScenario.value)
      ?? scenarios.value.find((scenario) => scenario.type === 'expected')
      ?? null,
  );
  const merchant = computed(() => state.analysis?.merchant ?? null);
  const assumptions = computed(() => state.analysis?.assumptions ?? null);

  function goToStep(step: WizardStep) {
    state.step = step;
  }

  async function analyze() {
    state.loading = true;
    state.error = null;

    try {
      state.analysis = await client.getScenarioAnalysis({
        merchant_id: state.merchantId,
        audience_size: state.audienceSize,
        fixed_campaign_cost: state.fixedCampaignCost,
        campaign_type: state.campaignType,
        campaign_conversion_rate: state.customConversionRate,
        parameters: state.campaignType === 'percentage_discount'
          ? { discount_percentage: state.discountPercentage }
          : { points_multiplier: state.pointsMultiplier },
      });
      state.step = 'analysis';
    } catch (error) {
      state.analysis = null;
      state.error = error instanceof Error ? error.message : 'The campaign could not be analyzed.';
    } finally {
      state.loading = false;
    }
  }

  function selectScenario(type: ScenarioType) {
    selectedScenario.value = type;
  }

  function setCustomConversionRate(value: number | null) {
    state.customConversionRate = value === null ? null : Number(value.toFixed(2));
  }

  async function applyCustomScenario() {
    selectedScenario.value = 'custom';
    await analyze();
    state.step = 'analysis';
    state.customConversionRate = null;
  }

  function reset() {
    state.step = 'merchant';
    state.analysis = null;
    state.error = null;
    selectedScenario.value = 'expected';
  }

  return {
    state,
    selectedScenario,
    scenarios,
    currentScenario,
    merchant,
    assumptions,
    goToStep,
    analyze,
    selectScenario,
    setCustomConversionRate,
    applyCustomScenario,
    reset,
  };
}

