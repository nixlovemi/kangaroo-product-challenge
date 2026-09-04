<script setup lang="ts">
import { computed, ref } from 'vue';
import type { ScenarioAnalysis, ScenarioType } from '../../types/campaign';
import { formatCurrency, formatPercentage, formatScenarioType } from '../../formatters/campaignFormatters';
import SegmentedControl, { type SegmentedOption } from '../library/SegmentedControl.vue';
import Modal from '../library/Modal.vue';

const props = defineProps<{
  scenarios: ScenarioAnalysis[];
  selectedScenario: ScenarioType;
  customScenario: ScenarioAnalysis | null;
  customConversionRate: number | null;
  currency: string;
}>();

const emit = defineEmits<{
  (event: 'select-scenario', type: ScenarioType): void;
  (event: 'update:custom', value: number | null): void;
  (event: 'apply-custom'): void;
}>();

const isCustomModalOpen = ref(false);

function formatValue(value: number): string {
  return formatCurrency(value, props.currency);
}

const scenarioOptions = computed<SegmentedOption[]>(() => props.scenarios.map((scenario) => ({
  value: scenario.type,
  label: formatScenarioType(scenario.type),
  sublabel: formatPercentage(scenario.campaign_conversion_rate),
  valueDisplay: formatValue(scenario.result.net_impact),
})));

function selectPreset(type: string) {
  emit('select-scenario', type as ScenarioType);
}

function openCustomModal() {
  isCustomModalOpen.value = true;
  emit('select-scenario', 'custom');
}

function closeCustomModal() {
  isCustomModalOpen.value = false;
}

function cancelCustomScenario() {
  emit('select-scenario', 'expected');
  closeCustomModal();
}

function setCustomConversionRate(event: Event) {
  const value = (event.target as HTMLInputElement).value;
  emit('update:custom', value === '' ? null : Number(value));
}

async function applyCustomScenario() {
  await emit('apply-custom');
  emit('select-scenario', 'custom');
  closeCustomModal();
}
</script>

<template>
  <div class="scenario-selector">
    <SegmentedControl
      :options="scenarioOptions"
      :model-value="props.selectedScenario"
      sr-label="Scenarios"
      dense
      option-class="scenario-option"
      @update:model-value="selectPreset"
    >
      <template #extra>
        <button
          type="button"
          class="segmented-control__option scenario-option scenario-option--custom"
          :class="{ 'is-active': props.selectedScenario === 'custom' }"
          @click="openCustomModal"
        >
          <span class="segmented-control__text">
            <strong>Custom</strong>
            <small>{{ props.customConversionRate === null ? 'Enter a rate' : formatPercentage(props.customConversionRate) }}</small>
          </span>
          <span class="segmented-control__value">{{ props.customScenario ? formatValue(props.customScenario.result.net_impact) : '✎' }}</span>
        </button>
      </template>
    </SegmentedControl>

    <Modal
      v-if="isCustomModalOpen"
      eyebrow="Custom scenario"
      title="Test a different audience response"
      sr-label="Custom scenario"
      @close="cancelCustomScenario"
    >
      <label class="field">
        <span>Total campaign conversion rate</span>
        <input
          type="number"
          min="0"
          max="100"
          step="0.01"
          class="custom-dialog__input"
          :value="props.customConversionRate ?? ''"
          @input="setCustomConversionRate"
          placeholder="Enter a custom conversion"
        >
        <small>This is the total campaign response for the scenario. The Campaign Advisor calculates the incremental orders from the merchant's historical data.</small>
      </label>

      <template #actions>
        <button type="button" class="secondary-button" @click="cancelCustomScenario">Cancel</button>
        <button type="button" class="primary-button" @click="applyCustomScenario">Apply scenario</button>
      </template>
    </Modal>

    <p class="field-hint">
      Each percentage represents the total campaign response for that scenario. Conservative uses 50% of the historical lift, expected uses 100%, strong response uses 150%, and custom lets the merchant test a different audience response.
    </p>
  </div>
</template>
