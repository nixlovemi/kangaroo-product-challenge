<script setup lang="ts">
const emit = defineEmits<{
  (event: 'choose-merchant', merchantId: number): void;
}>();
</script>

<template>
  <section class="step-card">
    <div class="step-intro">
      <p class="eyebrow">Step 1</p>
      <h2>Choose a merchant account</h2>
      <p>Pick the merchant whose historical activity will shape the recommendation.</p>
    </div>
    <div class="choice-grid">
      <button class="choice-card" type="button" @click="emit('choose-merchant', 101)">
        <span class="choice-kicker">Merchant 101</span>
        <strong>Atelier Nord Cafe</strong>
        <small>Balanced margins and steady repeat purchases</small>
      </button>
      <button class="choice-card" type="button" @click="emit('choose-merchant', 202)">
        <span class="choice-kicker">Merchant 202</span>
        <strong>Saint-Paul Market</strong>
        <small>Lower margin, but stronger points activity</small>
      </button>
    </div>
  </section>
</template>
