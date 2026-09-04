<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue';
import type { MerchantAssumptions, MerchantInfo, ScenarioAnalysis, ScenarioAnalysisData, ScenarioType } from '../../types/campaign';
import WizardActions from '../library/WizardActions.vue';

const ScenarioSelector = defineAsyncComponent(() => import('../analysis/ScenarioSelector.vue'));
const ScenarioSummaryCard = defineAsyncComponent(() => import('../analysis/ScenarioSummaryCard.vue'));
const MerchantAssumptionsAccordion = defineAsyncComponent(() => import('../analysis/MerchantAssumptionsAccordion.vue'));
const CalculationTrailAccordion = defineAsyncComponent(() => import('../analysis/CalculationTrailAccordion.vue'));
const RecommendationsAccordion = defineAsyncComponent(() => import('../analysis/RecommendationsAccordion.vue'));

const props = defineProps<{
  analysis: ScenarioAnalysisData | null;
  selectedScenario: ScenarioType;
  currentScenario: ScenarioAnalysis | null;
  customConversionRate: number | null;
  merchant: MerchantInfo | null;
  assumptions: MerchantAssumptions | null;
  error: string | null;
}>();

const emit = defineEmits<{
  (event: 'select-scenario', type: ScenarioType): void;
  (event: 'update:custom', value: number | null): void;
  (event: 'apply-custom'): void;
  (event: 'back'): void;
  (event: 'continue'): void;
}>();

const currency = computed(() => props.analysis?.merchant.currency ?? 'CAD');
const scenarios = computed(() => props.analysis?.scenarios ?? []);
const presetScenarios = computed(() => scenarios.value.filter((scenario) => scenario.type !== 'custom'));
const customScenario = computed(() => scenarios.value.find((scenario) => scenario.type === 'custom') ?? null);
</script>

<template>
  <section class="step-card">
    <div class="step-intro">
      <p class="eyebrow">Step 3</p>
      <h2>Compare scenarios and decide</h2>
      <p>Compare realistic outcomes and choose the safest launch condition.</p>
    </div>

    <ScenarioSelector
      :scenarios="presetScenarios"
      :selected-scenario="props.selectedScenario"
      :custom-scenario="customScenario"
      :custom-conversion-rate="props.customConversionRate"
      :currency="currency"
      @select-scenario="emit('select-scenario', $event)"
      @update:custom="emit('update:custom', $event)"
      @apply-custom="emit('apply-custom')"
    />

    <ScenarioSummaryCard :scenario="props.currentScenario" :currency="currency" @adjust="emit('back')" />

    <MerchantAssumptionsAccordion :merchant="merchant" :assumptions="assumptions" />

    <CalculationTrailAccordion :scenario="props.currentScenario" :currency="currency" />

    <RecommendationsAccordion :scenario="props.currentScenario" :currency="currency" />

    <p v-if="error" class="error-banner">{{ error }}</p>

    <WizardActions primary-label="Continue to review" @back="emit('back')" @primary="emit('continue')" />
  </section>
</template>
