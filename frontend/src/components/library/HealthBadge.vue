<script setup lang="ts">
import { computed } from 'vue';
import type { HealthStatus } from '../../types/campaign';
import { healthStatusLabel, healthStatusTone } from '../../formatters/campaignFormatters';

const props = defineProps<{
  status: HealthStatus | null;
}>();

const label = computed(() => props.status ? healthStatusLabel(props.status) : 'Pending');
const tone = computed(() => props.status ? healthStatusTone(props.status) : 'warning');
</script>

<template>
  <span class="health-badge" :class="`is-${tone}`">{{ label }}</span>
</template>

<style scoped>
.health-badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.health-badge.is-success {
  background: rgba(15, 159, 110, 0.12);
  color: var(--success, #0f9f6e);
}

.health-badge.is-warning {
  background: rgba(180, 83, 9, 0.12);
  color: var(--warning, #b45309);
}

.health-badge.is-danger {
  background: rgba(220, 38, 38, 0.12);
  color: var(--danger, #dc2626);
}
</style>
