<script setup lang="ts">
/**
 * Generic label/value/hint card used across metric, insight, compare and summary-stat
 * blocks. The `variant` prop selects which existing global CSS class to apply so the
 * visual identity of each call site stays exactly the same after extraction.
 */
withDefaults(defineProps<{
  label: string;
  value: string;
  hint?: string;
  variant?: 'metric' | 'insight' | 'compare' | 'compare-target' | 'stat';
}>(), {
  hint: '',
  variant: 'metric',
});

const variantClassMap = {
  metric: 'metric-card',
  insight: 'insight-card',
  compare: 'compare-card',
  'compare-target': 'compare-card compare-card--target',
  stat: 'summary-stat',
} as const;
</script>

<template>
  <div :class="variantClassMap[variant]">
    <span>{{ label }}</span>
    <strong>{{ value }}</strong>
    <small v-if="hint">{{ hint }}</small>
  </div>
</template>

<style scoped>
.metric-card,
.insight-card,
.compare-card {
  border: 1px solid #e2e8f0;
  border-radius: 22px;
  background: #fff;
}

.metric-card,
.insight-card {
  padding: 16px;
  min-height: 96px;
}

.compare-card {
  padding: 18px;
  border-radius: 18px;
}

.compare-card.compare-card--target {
  background: linear-gradient(180deg, #fff 0%, #eef2ff 100%);
  border-color: rgba(79, 70, 229, 0.25);
}

.summary-stat {
  display: grid;
  gap: 4px;
}

.metric-card span,
.insight-card span,
.compare-card span,
.summary-stat span {
  font-size: 0.85rem;
  color: #64748b;
}

.metric-card strong,
.insight-card strong {
  display: block;
  font-size: 1.15rem;
  margin-top: 6px;
}

.compare-card strong {
  display: block;
  font-size: 1.4rem;
  margin-top: 6px;
}

.summary-stat strong {
  font-size: 1.3rem;
}

.summary-stat small {
  color: #94a3b8;
  font-size: 0.78rem;
}
</style>
