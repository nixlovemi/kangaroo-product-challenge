<script setup lang="ts">
withDefaults(defineProps<{
  backLabel?: string;
  primaryLabel: string;
  primaryDisabled?: boolean;
  primaryVariant?: 'primary' | 'secondary';
}>(), {
  backLabel: 'Back',
  primaryDisabled: false,
  primaryVariant: 'primary',
});

const emit = defineEmits<{
  (event: 'back'): void;
  (event: 'primary'): void;
}>();
</script>

<template>
  <div class="actions-row">
    <button class="secondary-button" type="button" @click="emit('back')">{{ backLabel }}</button>
    <button
      :class="primaryVariant === 'primary' ? 'primary-button' : 'secondary-button'"
      type="button"
      :disabled="primaryDisabled"
      @click="emit('primary')"
    >
      {{ primaryLabel }}
    </button>
  </div>
</template>
