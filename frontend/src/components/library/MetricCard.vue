<script setup lang="ts">
/**
 * Generic label/value/hint card. The `variant` prop picks the visual treatment:
 * `metric` for grid cards, `stat` for the borderless figures inside a summary card.
 */
withDefaults(defineProps<{
  label: string;
  value: string;
  hint?: string;
  variant?: 'metric' | 'stat';
}>(), {
  hint: '',
  variant: 'metric',
});

const variantClassMap = {
  metric: 'metric-card',
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
.metric-card {
  border: 1px solid #e2e8f0;
  border-radius: 22px;
  background: #fff;
  padding: 16px;
  min-height: 96px;
}

.summary-stat {
  display: grid;
  gap: 4px;
}

.metric-card span,
.summary-stat span {
  font-size: 0.85rem;
  color: #64748b;
}

.metric-card strong {
  display: block;
  font-size: 1.15rem;
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
