<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue';
import { useCampaignAdvisor } from './composables/useCampaignAdvisor';
import { formatPercentage } from './formatters/campaignFormatters';

const AppShell = defineAsyncComponent(() => import('./components/AppShell.vue'));
const advisor = useCampaignAdvisor();
const state = advisor.state;
const campaignName = computed(() => state.campaignType === 'percentage_discount'
  ? `${formatPercentage(state.discountPercentage)} discount campaign`
  : 'Double points campaign');
</script>

<template>
  <AppShell
    :state="state"
    :analysis="state.analysis"
    :selected-scenario="advisor.selectedScenario.value"
    :current-scenario="advisor.currentScenario.value"
    :merchant="advisor.merchant.value"
    :assumptions="advisor.assumptions.value"
    :campaign-name="campaignName"
    @go-to-step="advisor.goToStep"
    @analyze="advisor.analyze"
    @select-scenario="advisor.selectScenario"
    @reset="advisor.reset"
    @update:merchant="state.merchantId = $event"
    @update:audience="state.audienceSize = $event"
    @update:cost="state.fixedCampaignCost = $event"
    @update:type="state.campaignType = $event"
    @update:discount="state.discountPercentage = $event"
    @update:multiplier="state.pointsMultiplier = $event"
    @update:custom="state.customConversionRate = $event"
  />
</template>
