<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue';
import type { CampaignDraftSettings, CampaignType, MerchantAssumptions, MerchantInfo, MerchantOverviewData, ScenarioAnalysis, ScenarioAnalysisData, ScenarioType } from '../types/campaign';
import { formatPercentage } from '../formatters/campaignFormatters';

const WizardHeader = defineAsyncComponent(() => import('./WizardHeader.vue'));
const MerchantStep = defineAsyncComponent(() => import('./steps/MerchantStep.vue'));
const CampaignDraftStep = defineAsyncComponent(() => import('./steps/CampaignDraftStep.vue'));
const AnalysisStep = defineAsyncComponent(() => import('./steps/AnalysisStep.vue'));
const ReviewStep = defineAsyncComponent(() => import('./steps/ReviewStep.vue'));

type WizardStep = 'merchant' | 'campaign' | 'analysis' | 'review';

interface CampaignState {
  step: WizardStep;
  merchantId: number;
  audienceSize: number;
  fixedCampaignCost: number;
  campaignType: CampaignType;
  discountPercentage: number;
  pointsMultiplier: number;
  customConversionRate: number | null;
  merchantOverview: MerchantOverviewData | null;
  analysis: ScenarioAnalysisData | null;
  loading: boolean;
  error: string | null;
}

const props = defineProps<{
  state: CampaignState;
  analysis: ScenarioAnalysisData | null;
  selectedScenario: ScenarioType;
  currentScenario: ScenarioAnalysis | null;
  merchant: MerchantInfo | null;
  assumptions: MerchantAssumptions | null;
  campaignName: string;
}>();

const emit = defineEmits<{
  (event: 'go-to-step', step: WizardStep): void;
  (event: 'analyze'): void;
  (event: 'select-scenario', type: ScenarioType | null): void;
  (event: 'reset'): void;
  (event: 'update:merchant', value: number): void;
  (event: 'update:audience', value: number): void;
  (event: 'update:cost', value: number): void;
  (event: 'update:type', value: CampaignType): void;
  (event: 'update:discount', value: number): void;
  (event: 'update:multiplier', value: number): void;
  (event: 'update:custom', value: number | null): void;
  (event: 'apply-custom'): void;
}>();

const currency = computed(() => props.analysis?.merchant.currency ?? 'CAD');

// Preview of the response rate to show on the draft step, before the backend simulation runs.
const summaryResponseRate = computed(() => {
  const expected = props.state.merchantOverview?.expected_conversion_rate;
  const rate = props.state.customConversionRate ?? expected ?? null;

  if (rate === null) {
    return '—';
  }

  if (expected !== undefined && expected !== rate) {
    return `${formatPercentage(rate)} (default: ${formatPercentage(expected)})`;
  }

  return formatPercentage(rate);
});

function chooseMerchant(merchantId: number) {
  emit('update:merchant', merchantId);
  emit('go-to-step', 'campaign');
}

// Groups the Step 2 draft fields so CampaignDraftStep/ReviewStep take one prop
// instead of five, translating a single update:draft back into the granular
// emits the composable already expects.
const draft = computed<CampaignDraftSettings>(() => ({
  audienceSize: props.state.audienceSize,
  fixedCampaignCost: props.state.fixedCampaignCost,
  campaignType: props.state.campaignType,
  discountPercentage: props.state.discountPercentage,
  pointsMultiplier: props.state.pointsMultiplier,
}));

function updateDraft(value: CampaignDraftSettings) {
  emit('update:audience', value.audienceSize);
  emit('update:cost', value.fixedCampaignCost);
  emit('update:type', value.campaignType);
  emit('update:discount', value.discountPercentage);
  emit('update:multiplier', value.pointsMultiplier);
}
</script>

<template>
  <main class="app-shell">
    <WizardHeader :step="props.state.step" />

    <MerchantStep v-if="props.state.step === 'merchant'" @choose-merchant="chooseMerchant" />

    <CampaignDraftStep
      v-else-if="props.state.step === 'campaign'"
      :draft="draft"
      :custom-conversion-rate="props.state.customConversionRate"
      :expected-conversion-rate="props.state.merchantOverview?.expected_conversion_rate ?? null"
      :loading="props.state.loading"
      :currency="currency"
      :campaign-name="props.campaignName"
      :summary-response-rate="summaryResponseRate"
      @update:draft="updateDraft"
      @update:custom="emit('update:custom', $event)"
      @back="emit('go-to-step', 'merchant')"
      @analyze="emit('analyze')"
    />

    <AnalysisStep
      v-else-if="props.state.step === 'analysis'"
      :analysis="props.analysis"
      :selected-scenario="props.selectedScenario"
      :current-scenario="props.currentScenario"
      :custom-conversion-rate="props.state.customConversionRate"
      :merchant="props.merchant"
      :assumptions="props.assumptions"
      :error="props.state.error"
      @select-scenario="emit('select-scenario', $event)"
      @update:custom="emit('update:custom', $event)"
      @apply-custom="emit('apply-custom')"
      @back="emit('go-to-step', 'campaign')"
      @continue="emit('go-to-step', 'review')"
    />

    <ReviewStep
      v-else
      :draft="draft"
      :currency="currency"
      :campaign-name="props.campaignName"
      :merchant="props.merchant"
      :scenario="props.currentScenario"
      @back="emit('go-to-step', 'analysis')"
      @reset="emit('reset')"
    />
  </main>
</template>
