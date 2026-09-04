<script setup lang="ts">
import { computed } from 'vue';
import { formatInteger, formatPercentage } from '../../formatters/campaignFormatters';
import type { CampaignType } from '../../types/campaign';
import SegmentedControl, { type SegmentedOption } from '../library/SegmentedControl.vue';
import WizardActions from '../library/WizardActions.vue';

const props = defineProps<{
  audienceSize: number;
  fixedCampaignCost: number;
  campaignType: CampaignType;
  discountPercentage: number;
  pointsMultiplier: number;
  customConversionRate: number | null;
  expectedConversionRate: number | null;
  loading: boolean;
  currency: string;
  campaignName: string;
  summaryResponseRate: string;
}>();

const emit = defineEmits<{
  (event: 'update:audience', value: number): void;
  (event: 'update:cost', value: number): void;
  (event: 'update:type', value: CampaignType): void;
  (event: 'update:discount', value: number): void;
  (event: 'update:multiplier', value: number): void;
  (event: 'update:custom', value: number | null): void;
  (event: 'back'): void;
  (event: 'analyze'): void;
}>();

const campaignTypeOptions: SegmentedOption[] = [
  { value: 'percentage_discount', label: 'Percentage discount', sublabel: 'Simple and direct incentive', icon: '%' },
  { value: 'double_points', label: 'Double points', sublabel: 'Reward loyalty without a price cut', icon: '2×' },
  { value: 'fixed_amount', label: 'Fixed amount', sublabel: 'Coming soon', icon: '$', disabled: true },
  { value: 'bogo', label: 'Buy one get one', sublabel: 'Coming soon', icon: '+', disabled: true },
];

function setCampaignType(value: string) {
  emit('update:type', value as CampaignType);
}

function setAudienceSize(event: Event) {
  emit('update:audience', Number((event.target as HTMLInputElement).value));
}

function setFixedCampaignCost(event: Event) {
  emit('update:cost', Number((event.target as HTMLInputElement).value));
}

function setDiscountPercentage(event: Event) {
  emit('update:discount', Number((event.target as HTMLInputElement).value));
}

function setPointsMultiplier(event: Event) {
  emit('update:multiplier', Number((event.target as HTMLInputElement).value));
}

function setCustomConversionRate(event: Event) {
  const value = (event.target as HTMLInputElement).value;
  emit('update:custom', value === '' ? null : Number(value));
}

const summaryCopy = computed(() => props.campaignType === 'percentage_discount'
  ? `A discount campaign targeting ${formatInteger(props.audienceSize)} people with an estimated response of ${props.summaryResponseRate}.`
  : `A double-points campaign targeting ${formatInteger(props.audienceSize)} people with an estimated response of ${props.summaryResponseRate}.`);

const responseRateHint = computed(() => {
  const base = "This is the total campaign conversion rate. The Campaign Advisor uses the merchant's historical data to estimate the incremental orders from that response.";

  if (props.expectedConversionRate === null) {
    return base;
  }

  return `${base} Leave it blank to use this merchant's default estimate of ${formatPercentage(props.expectedConversionRate)}.`;
});
</script>

<template>
  <section class="step-card">
    <div class="step-intro">
      <p class="eyebrow">Step 2</p>
      <h2>Review the campaign draft</h2>
      <p>This screen matches the current Kangaroo flow: the merchant already chose the campaign parameters, and the advisor only reviews the financial impact.</p>
    </div>

    <div class="draft-grid">
      <div class="field-group">
        <label class="field">
          <span>Audience size</span>
          <input type="number" min="1" step="1" :value="props.audienceSize" @input="setAudienceSize">
        </label>
        <label class="field">
          <span>Fixed campaign cost ({{ props.currency }})</span>
          <input type="number" min="0" step="0.01" :value="props.fixedCampaignCost" @input="setFixedCampaignCost">
        </label>
        <div class="field field--choice">
          <span>Campaign type</span>
          <SegmentedControl
            :options="campaignTypeOptions"
            :model-value="props.campaignType"
            sr-label="Campaign type"
            @update:model-value="setCampaignType"
          />
          <small class="field-hint">More campaign types are coming soon.</small>
        </div>
      </div>

      <div class="field-group">
        <label v-if="props.campaignType === 'percentage_discount'" class="field">
          <span>Discount percentage</span>
          <input type="number" min="0" max="100" step="0.01" :value="props.discountPercentage" @input="setDiscountPercentage">
        </label>
        <label v-else class="field">
          <span>Points multiplier</span>
          <input type="number" min="1" max="10" step="0.1" :value="props.pointsMultiplier" @input="setPointsMultiplier">
        </label>
        <label class="field">
          <span>Estimated audience response (optional)</span>
          <input
            type="number"
            min="0"
            max="100"
            step="0.01"
            :value="props.customConversionRate ?? ''"
            :placeholder="props.expectedConversionRate !== null ? `Default: ${props.expectedConversionRate}` : undefined"
            @input="setCustomConversionRate"
          >
          <small class="field-hint">{{ responseRateHint }}</small>
        </label>
        <div class="hint-card">
          <strong>{{ props.campaignName }}: </strong>
          <span>{{ summaryCopy }}</span>
        </div>
      </div>
    </div>

    <WizardActions
      primary-label="Start campaign analysis"
      :primary-disabled="props.loading"
      @back="emit('back')"
      @primary="emit('analyze')"
    />
  </section>
</template>
