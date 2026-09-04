<script setup lang="ts">
import type { ScenarioAnalysis } from '../../types/campaign';
import { formatCurrency, formatPercentage } from '../../formatters/campaignFormatters';
import MetricCard from '../library/MetricCard.vue';

const props = defineProps<{
  scenario: ScenarioAnalysis | null;
  currency: string;
}>();

function formatValue(value: number): string {
  return formatCurrency(value, props.currency);
}
</script>

<template>
  <div class="insight-grid">
    <MetricCard
      variant="insight"
      label="Incremental revenue"
      :value="scenario ? formatValue(scenario.result.incremental_revenue) : '—'"
    />
    <MetricCard
      variant="insight"
      label="Incentive cost"
      :value="scenario ? formatValue(scenario.result.incentive_cost) : '—'"
    />
    <MetricCard
      variant="insight"
      label="ROI"
      :value="scenario && scenario.result.roi !== null ? formatPercentage(scenario.result.roi) : '—'"
    />
    <MetricCard
      variant="insight"
      label="Break-even conversion"
      :value="scenario ? formatPercentage(scenario.result.break_even_conversion_rate) : '—'"
    />
  </div>
</template>

<style scoped>
/* Layout is scoped so this block can be swapped for a chart component later
   without hunting for its grid rules in the global stylesheet. */
.insight-grid {
  display: grid;
  gap: 18px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

@media (max-width: 1100px) {
  .insight-grid {
    grid-template-columns: 1fr;
  }
}
</style>
