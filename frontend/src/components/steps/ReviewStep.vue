<script setup lang="ts">
import type { CampaignType, MerchantInfo, ScenarioAnalysis } from '../../types/campaign';
import { formatCurrency, formatInteger, formatPercentage } from '../../formatters/campaignFormatters';
import HealthBadge from '../library/HealthBadge.vue';
import WizardActions from '../library/WizardActions.vue';

const props = defineProps<{
  audienceSize: number;
  fixedCampaignCost: number;
  campaignType: CampaignType;
  currency: string;
  campaignName: string;
  merchant: MerchantInfo | null;
  scenario: ScenarioAnalysis | null;
}>();

defineEmits<{
  (event: 'back'): void;
  (event: 'reset'): void;
}>();

function formatValue(value: number): string {
  return formatCurrency(value, props.currency);
}
</script>

<template>
  <section class="step-card">
    <div class="step-intro">
      <p class="eyebrow">Step 4</p>
      <h2>Final review</h2>
      <p>Confirm the campaign with the recommendation and the most relevant scenario in view.</p>
    </div>

    <div class="review-grid">
      <article class="panel-card">
        <header class="section-header">
          <div>
            <p class="eyebrow">Campaign summary</p>
            <h3>{{ campaignName }}</h3>
          </div>
        </header>
        <ul class="summary-list">
          <li><span>Merchant</span><strong>{{ merchant?.name ?? '—' }}</strong></li>
          <li><span>Audience</span><strong>{{ formatInteger(audienceSize) }}</strong></li>
          <li><span>Fixed cost</span><strong>{{ formatValue(fixedCampaignCost) }}</strong></li>
          <li><span>Campaign type</span><strong>{{ campaignType }}</strong></li>
        </ul>
      </article>

      <article class="panel-card">
        <header class="section-header">
          <div>
            <p class="eyebrow">Decision summary</p>
            <h3>What the advisor recommends</h3>
          </div>
        </header>
        <ul class="summary-list">
          <li><span>Health</span><HealthBadge :status="scenario?.result.health_status ?? null" /></li>
          <li><span>Net impact</span><strong>{{ scenario ? formatValue(scenario.result.net_impact) : '—' }}</strong></li>
          <li><span>Break-even</span><strong>{{ scenario ? formatPercentage(scenario.result.break_even_conversion_rate) : '—' }}</strong></li>
          <li><span>ROI</span><strong>{{ scenario && scenario.result.roi !== null ? formatPercentage(scenario.result.roi) : '—' }}</strong></li>
        </ul>
      </article>
    </div>

    <WizardActions
      back-label="Back to analysis"
      primary-label="Start over"
      primary-variant="secondary"
      @back="$emit('back')"
      @primary="$emit('reset')"
    />
  </section>
</template>
