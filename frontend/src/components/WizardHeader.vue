<script setup lang="ts">
type WizardStep = 'merchant' | 'campaign' | 'analysis' | 'review';

defineProps<{
  step: WizardStep;
}>();
</script>

<template>
  <header class="wizard-header">
    <div>
      <p class="eyebrow">Kangaroo Campaign Advisor</p>
      <h1>Campaign outcomes for merchants</h1>
      <p class="hero-copy">
        This demo simulates two merchants with different buying patterns and uses their historical data to review the next campaign before launch.
      </p>
    </div>
    <ol class="stepper">
      <li :class="{ 'is-active': step === 'merchant' }">Merchant</li>
      <li :class="{ 'is-active': step === 'campaign' }">Campaign</li>
      <li :class="{ 'is-active': step === 'analysis' }">Analysis</li>
      <li :class="{ 'is-active': step === 'review' }">Review</li>
    </ol>
  </header>
</template>
