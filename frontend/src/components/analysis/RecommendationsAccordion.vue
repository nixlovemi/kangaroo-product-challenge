<script setup lang="ts">
import { computed } from 'vue';
import type { Recommendation, ScenarioAnalysis } from '../../types/campaign';
import { formatCurrency, formatInteger, formatPercentage } from '../../formatters/campaignFormatters';

const props = defineProps<{
  scenario: ScenarioAnalysis | null;
  currency: string;
}>();

const recommendations = computed(() => props.scenario?.recommendations ?? null);
const items = computed(() => recommendations.value?.items ?? []);
const actionableCount = computed(() => items.value.filter((item) => item.outcome === 'actionable').length);

function formatLeverValue(item: Recommendation, value: number): string {
  if (item.value_type === 'currency') {
    return formatCurrency(value, props.currency);
  }

  if (item.value_type === 'percentage') {
    return formatPercentage(value);
  }

  if (item.value_type === 'multiplier') {
    return `${value.toFixed(2)}x`;
  }

  return formatInteger(value);
}

const headline = computed(() => {
  if (recommendations.value?.already_meets_target) {
    return 'No changes needed';
  }

  return actionableCount.value > 0
    ? `${actionableCount.value} change${actionableCount.value > 1 ? 's' : ''} would make this work`
    : 'Why no tweak fixes this';
});
</script>

<template>
  <details v-if="recommendations" class="accordion recommendations">
    <summary class="accordion__summary">
      <span class="eyebrow">How to improve it</span>
      <h3>{{ headline }}</h3>
    </summary>

    <div class="accordion__body">
      <p class="recommendations__summary">{{ recommendations.summary_message }}</p>

      <ul v-if="items.length" class="recommendations__list">
        <li
          v-for="item in items"
          :key="item.lever"
          class="recommendation"
          :class="`is-${item.outcome}`"
        >
          <div class="recommendation__head">
            <span class="recommendation__label">{{ item.label }}</span>
            <span v-if="item.suggested_value !== null" class="recommendation__change">
              {{ formatLeverValue(item, item.current_value) }}
              <span aria-hidden="true">→</span>
              <strong>{{ formatLeverValue(item, item.suggested_value) }}</strong>
            </span>
            <span v-else class="recommendation__change recommendation__change--none">
              {{ formatLeverValue(item, item.current_value) }} — no viable change
            </span>
          </div>
          <p class="recommendation__message">{{ item.message }}</p>
        </li>
      </ul>
    </div>
  </details>
</template>

<style scoped>
.recommendations__summary {
  font-size: 0.92rem;
  color: #1e293b;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 12px 14px;
}

.recommendations__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 10px;
}

.recommendation {
  border: 1px solid #e2e8f0;
  border-left-width: 4px;
  border-radius: 14px;
  padding: 12px 14px;
  background: #fff;
  display: grid;
  gap: 6px;
}

/* Actionable rows lead; diagnoses stay legible but visually recede. */
.recommendation.is-actionable {
  border-left-color: #22c55e;
}

.recommendation.is-implausible,
.recommendation.is-infeasible {
  border-left-color: #cbd5e1;
  background: #fcfcfd;
}

.recommendation.is-implausible .recommendation__message,
.recommendation.is-infeasible .recommendation__message {
  color: #64748b;
}

.recommendation__head {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.recommendation__label {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.92rem;
}

.recommendation__change {
  font-size: 0.9rem;
  color: #475569;
  white-space: nowrap;
}

.recommendation__change--none {
  color: #94a3b8;
}

.recommendation__message {
  font-size: 0.85rem;
  color: #475569;
}
</style>
