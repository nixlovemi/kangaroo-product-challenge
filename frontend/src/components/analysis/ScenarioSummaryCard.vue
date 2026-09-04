<script setup lang="ts">
import { computed } from 'vue';
import type { ScenarioAnalysis } from '../../types/campaign';
import { formatCurrency, formatInteger, formatPercentage } from '../../formatters/campaignFormatters';
import HealthBadge from '../library/HealthBadge.vue';
import MetricCard from '../library/MetricCard.vue';

const props = defineProps<{
  scenario: ScenarioAnalysis | null;
  currency: string;
}>();

defineEmits<{
  (event: 'adjust'): void;
}>();

function formatValue(value: number): string {
  return formatCurrency(value, props.currency);
}

const ordersNeededLabel = computed(() => {
  if (!props.scenario) return '—';
  return `${formatInteger(props.scenario.result.insight.break_even_incremental_orders)} incremental`;
});

const ordersNeededHint = computed(() => {
  if (!props.scenario) return '';
  return `${formatPercentage(props.scenario.result.break_even_conversion_rate)} of audience total`;
});
</script>

<template>
  <transition name="fade" mode="out-in">
    <article v-if="scenario" :key="scenario.type" class="summary-card summary-card--full">
      <div class="summary-card__head">
        <HealthBadge :status="scenario.result.health_status" />
        <span class="summary-tag">Scenario result</span>
      </div>

      <h2>{{ formatValue(scenario.result.net_impact) }}</h2>
      <p class="summary-subtitle">Estimated profit or loss if this scenario plays out as projected</p>

      <p class="summary-driver">{{ scenario.result.insight.health_driver_message }}</p>

      <div class="summary-stats">
        <MetricCard
          variant="stat"
          label="Incremental orders"
          :value="formatInteger(scenario.result.incremental_orders)"
          :hint="scenario.result.insight.orders_context_message"
        />
        <MetricCard
          variant="stat"
          label="Orders needed to break even"
          :value="ordersNeededLabel"
          :hint="ordersNeededHint"
        />
      </div>

      <div class="progress-track" aria-label="Break-even progress">
        <div class="progress-fill" :style="{ width: `${scenario.result.insight.break_even_progress_percentage}%` }" />
      </div>
      <p class="hint">
        Projected conversion {{ formatPercentage(scenario.campaign_conversion_rate) }} vs
        break-even {{ formatPercentage(scenario.result.break_even_conversion_rate) }}
      </p>

      <div class="summary-action" :class="{ 'is-positive': scenario.result.health_status === 'healthy' }">
        <p>{{ scenario.result.insight.action_message }}</p>
        <button
          v-if="scenario.result.health_status !== 'healthy'"
          type="button"
          class="summary-action__cta"
          @click="$emit('adjust')"
        >
          ✏️ Adjust campaign parameters
        </button>
      </div>
    </article>
  </transition>
</template>

<style scoped>
.summary-card {
  border: 1px solid #e2e8f0;
  border-radius: 22px;
  background: #fff;
  padding: 24px;
  display: grid;
  gap: 14px;
}

.summary-card--full {
  width: 100%;
}

.summary-card h2 {
  font-size: 2.4rem;
}

.summary-card p {
  color: #475569;
}

.summary-card__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.summary-tag {
  font-size: 0.8rem;
  color: #64748b;
}

.summary-subtitle {
  color: #64748b;
  font-size: 0.9rem;
  margin-top: -6px;
}

.summary-driver {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 12px 14px;
  font-size: 0.92rem;
  color: #1e293b;
}

.summary-stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.summary-action {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
  border-top: 1px dashed #e2e8f0;
  padding-top: 14px;
}

.summary-action p {
  margin: 0;
  font-size: 0.9rem;
  font-weight: 600;
  color: #b45309;
}

.summary-action.is-positive p {
  color: #15803d;
}

.summary-action__cta {
  border: 1px solid #6366f1;
  background: #eef2ff;
  color: #4338ca;
  border-radius: 999px;
  padding: 8px 16px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
}

.summary-action__cta:hover {
  background: #e0e7ff;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(4px);
}
</style>

