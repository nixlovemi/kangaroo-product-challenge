<script setup lang="ts">
export interface SegmentedOption {
  value: string;
  label: string;
  sublabel?: string;
  icon?: string;
  valueDisplay?: string;
  disabled?: boolean;
}

withDefaults(defineProps<{
  options: SegmentedOption[];
  modelValue: string;
  srLabel: string;
  dense?: boolean;
  optionClass?: string;
}>(), {
  dense: false,
  optionClass: '',
});

const emit = defineEmits<{
  (event: 'update:modelValue', value: string): void;
}>();
</script>

<template>
  <div class="segmented-control" :class="{ 'segmented-control--scenarios': dense }" role="tablist" :aria-label="srLabel">
    <button
      v-for="option in options"
      :key="option.value"
      type="button"
      class="segmented-control__option"
      :class="[optionClass, { 'is-active': option.value === modelValue, 'segmented-control__option--muted': option.disabled }]"
      :disabled="option.disabled"
      @click="emit('update:modelValue', option.value)"
    >
      <span v-if="option.icon" class="segmented-control__icon">{{ option.icon }}</span>
      <span class="segmented-control__text">
        <strong>{{ option.label }}</strong>
        <small v-if="option.sublabel">{{ option.sublabel }}</small>
      </span>
      <span v-if="!option.icon && option.valueDisplay !== undefined" class="segmented-control__value">{{ option.valueDisplay }}</span>
    </button>

    <slot name="extra" />
  </div>
</template>
