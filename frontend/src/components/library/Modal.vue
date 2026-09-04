<script setup lang="ts">
withDefaults(defineProps<{
  eyebrow?: string;
  title: string;
  srLabel: string;
}>(), {
  eyebrow: '',
});

const emit = defineEmits<{
  (event: 'close'): void;
}>();
</script>

<template>
  <div class="custom-dialog-backdrop" role="presentation" @click.self="emit('close')">
    <div class="custom-dialog" role="dialog" aria-modal="true" :aria-label="srLabel">
      <div class="custom-dialog__head">
        <div>
          <p v-if="eyebrow" class="eyebrow">{{ eyebrow }}</p>
          <h3>{{ title }}</h3>
        </div>
        <button type="button" class="custom-dialog__close" @click="emit('close')">×</button>
      </div>

      <slot />

      <div class="custom-dialog__actions">
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>
