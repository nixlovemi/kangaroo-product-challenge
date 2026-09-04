<script setup lang="ts">
import type { MerchantAssumptions, ScenarioAnalysis } from '../../types/campaign';
import { formatPercentage } from '../../formatters/campaignFormatters';
import MetricCard from '../library/MetricCard.vue';

defineProps<{
  assumptions: MerchantAssumptions | null;
  scenario: ScenarioAnalysis | null;
}>();
</script>

<template>
  <div class="panel-card">
    <div class="compare-grid">
      <MetricCard
        variant="compare"
        label="Historical conversion"
        :value="assumptions ? formatPercentage(assumptions.historical_conversion_rate) : '—'"
      />
      <MetricCard
        variant="compare-target"
        label="Break-even conversion"
        :value="scenario ? formatPercentage(scenario.result.break_even_conversion_rate) : '—'"
      />
    </div>
    <div class="progress-track progress-track--compare">
      <div class="progress-fill" :style="{ width: `${scenario ? scenario.result.insight.break_even_progress_percentage : 0}%` }" />
    </div>
  </div>
</template>

<style scoped>
/* Layout is scoped so this block can be swapped for a chart component later
   without hunting for its grid rules in the global stylesheet. */
.compare-grid {
  display: grid;
  gap: 16px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.progress-track--compare {
  margin-top: 10px;
}
</style>
