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
        This demo simulates two merchants with distinct buying patterns, applying their historical baselines to project how a new campaign might perform under different scenarios.
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
