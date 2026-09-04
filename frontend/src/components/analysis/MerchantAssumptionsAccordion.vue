<script setup lang="ts">
import { computed } from 'vue';
import type { MerchantAssumptions, MerchantInfo } from '../../types/campaign';
import { formatCurrency, formatPercentage } from '../../formatters/campaignFormatters';
import MetricCard from '../library/MetricCard.vue';

const props = defineProps<{
  merchant: MerchantInfo | null;
  assumptions: MerchantAssumptions | null;
}>();

function formatValue(value: number): string {
  return formatCurrency(value, props.merchant?.currency ?? 'CAD');
}

const metrics = computed(() => [
  { label: 'Average order value', value: props.assumptions ? formatValue(props.assumptions.average_order_value) : '—' },
  { label: 'Gross margin', value: props.assumptions ? formatPercentage(props.assumptions.gross_margin_percentage) : '—' },
  { label: 'Historical conversion', value: props.assumptions ? formatPercentage(props.assumptions.historical_conversion_rate) : '—' },
  { label: 'Historical lift', value: props.assumptions ? formatPercentage(props.assumptions.historical_campaign_lift_percentage) : '—' },
]);
</script>

<template>
  <details class="accordion">
    <summary class="accordion__summary">
      <span class="eyebrow">Merchant assumptions</span>
      <h3>{{ merchant?.name ?? 'Merchant profile' }}</h3>
    </summary>

    <div class="accordion__body">
      <div class="metrics-grid">
        <MetricCard v-for="metric in metrics" :key="metric.label" variant="metric" :label="metric.label" :value="metric.value" />
      </div>

      <p class="hint-card">
        Historical data is used to estimate the incremental share of the campaign response. The field above shows total campaign conversion; the incremental orders are derived from the merchant's baseline activity.
      </p>
    </div>
  </details>
</template>
