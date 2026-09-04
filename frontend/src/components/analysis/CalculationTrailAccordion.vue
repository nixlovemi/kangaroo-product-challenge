<script setup lang="ts">
import { computed } from 'vue';
import type { CalculationStep, ScenarioAnalysis } from '../../types/campaign';
import { formatCurrency, formatInteger, formatPercentage } from '../../formatters/campaignFormatters';

const props = defineProps<{
  scenario: ScenarioAnalysis | null;
  currency: string;
}>();

const steps = computed(() => props.scenario?.result.calculation_steps ?? []);

function formatStepValue(step: CalculationStep): string {
  if (step.value_type === 'currency') {
    return formatCurrency(step.value, props.currency);
  }

  if (step.value_type === 'percentage') {
    return formatPercentage(step.value);
  }

  return formatInteger(step.value);
}

// The bottom-line step is highlighted so the trail reads as "…and this is why".
function isOutcomeStep(step: CalculationStep): boolean {
  return step.label === 'Net impact';
}
</script>

<template>
  <details v-if="steps.length" class="accordion calculation-trail">
    <summary class="accordion__summary">
      <span class="eyebrow">How we got here</span>
      <h3>Step-by-step calculation</h3>
    </summary>

    <div class="accordion__body">
      <ol class="trail">
        <li v-for="(step, index) in steps" :key="step.label" class="trail__step" :class="{ 'is-outcome': isOutcomeStep(step) }">
          <span class="trail__index">{{ index + 1 }}</span>
          <div class="trail__content">
            <p class="trail__label">{{ step.label }}</p>
            <p class="trail__formula">{{ step.formula }}</p>
          </div>
          <strong class="trail__value">{{ formatStepValue(step) }}</strong>
        </li>
      </ol>

      <p class="hint-card">
        Every figure above is produced by the Campaign Advisor from the merchant's historical data and the campaign parameters — no rounding happens between steps.
      </p>
    </div>
  </details>
</template>

<style scoped>
.trail {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 10px;
}

.trail__step {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr) auto;
  gap: 14px;
  align-items: center;
  padding: 12px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  background: #fff;
}

.trail__step.is-outcome {
  border-color: rgba(79, 70, 229, 0.35);
  background: linear-gradient(180deg, #fff 0%, #eef2ff 100%);
}

.trail__index {
  display: inline-flex;
  width: 26px;
  height: 26px;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  background: #f1f5f9;
  color: #64748b;
  font-size: 0.78rem;
  font-weight: 700;
}

.trail__step.is-outcome .trail__index {
  background: rgba(79, 70, 229, 0.12);
  color: #4338ca;
}

.trail__content {
  display: grid;
  gap: 2px;
  min-width: 0;
}

.trail__label {
  font-size: 0.92rem;
  font-weight: 600;
  color: #1e293b;
}

.trail__formula {
  font-size: 0.8rem;
  color: #64748b;
}

.trail__value {
  font-size: 1.05rem;
  white-space: nowrap;
}

@media (max-width: 640px) {
  .trail__step {
    grid-template-columns: auto minmax(0, 1fr);
  }

  .trail__value {
    grid-column: 2;
  }
}
</style>
