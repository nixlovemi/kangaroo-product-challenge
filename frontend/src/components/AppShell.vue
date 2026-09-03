<script setup lang="ts">
import { computed, ref } from 'vue';
import type { CampaignType, MerchantAssumptions, MerchantInfo, ScenarioAnalysisData, ScenarioType, SimulationMetrics } from '../types/campaign';
import { formatCurrency, formatInteger, formatPercentage, healthStatusLabel, healthStatusTone } from '../formatters/campaignFormatters';

type WizardStep = 'merchant' | 'campaign' | 'analysis' | 'review';

interface CampaignState {
  step: WizardStep;
  merchantId: number;
  audienceSize: number;
  fixedCampaignCost: number;
  campaignType: CampaignType;
  discountPercentage: number;
  pointsMultiplier: number;
  customConversionRate: number | null;
  analysis: ScenarioAnalysisData | null;
  loading: boolean;
  error: string | null;
}

const props = defineProps<{
  state: CampaignState;
  analysis: ScenarioAnalysisData | null;
  selectedScenario: ScenarioType;
  currentScenario: { type: ScenarioType; campaign_conversion_rate: number; result: SimulationMetrics } | null;
  merchant: MerchantInfo | null;
  assumptions: MerchantAssumptions | null;
  campaignName: string;
}>();

const emit = defineEmits<{
  (event: 'go-to-step', step: WizardStep): void;
  (event: 'analyze'): void;
  (event: 'select-scenario', type: ScenarioType | null): void;
  (event: 'reset'): void;
  (event: 'update:merchant', value: number): void;
  (event: 'update:audience', value: number): void;
  (event: 'update:cost', value: number): void;
  (event: 'update:type', value: CampaignType): void;
  (event: 'update:discount', value: number): void;
  (event: 'update:multiplier', value: number): void;
  (event: 'update:custom', value: number | null): void;
  (event: 'apply-custom'): void;
}>();

const currency = computed(() => props.analysis?.merchant.currency ?? 'CAD');
const scenarios = computed(() => props.analysis?.scenarios ?? []);
const customScenario = computed(() => scenarios.value.find(s => s.type === 'custom') || null);
const activeScenario = computed(() => props.currentScenario);
const activeTone = computed(() => activeScenario.value ? healthStatusTone(activeScenario.value.result.health_status) : 'warning');
const activeHealthLabel = computed(() => activeScenario.value ? healthStatusLabel(activeScenario.value.result.health_status) : 'Pending');
const breakEvenProgress = computed(() => {
  if (!activeScenario.value) return 0;
  const ratio = activeScenario.value.campaign_conversion_rate / Math.max(activeScenario.value.result.break_even_conversion_rate, 0.01);
  return Math.min(100, Math.max(0, ratio * 100));
});

// How many incremental orders (beyond baseline) are needed to reach the break-even conversion rate.
const breakEvenIncrementalOrders = computed(() => {
  if (!activeScenario.value) return 0;
  const breakEvenTotalOrders = Math.round((activeScenario.value.result.break_even_conversion_rate / 100) * props.state.audienceSize);
  return Math.max(0, breakEvenTotalOrders - activeScenario.value.result.baseline_orders);
});

// Plain-language explanation of why the scenario is healthy/caution/risky.
const driverMessage = computed(() => {
  if (!activeScenario.value) return '';
  const { health_status } = activeScenario.value.result;
  const projected = formatPercentage(activeScenario.value.campaign_conversion_rate);
  const breakEven = formatPercentage(activeScenario.value.result.break_even_conversion_rate);

  if (health_status === 'risky') {
    return `Risky because the projected conversion (${projected}) is below the ${breakEven} needed to cover incentive and campaign costs.`;
  }

  if (health_status === 'caution') {
    return `Projected to break even, but with little safety margin above the ${breakEven} conversion needed to cover costs.`;
  }

  return `Healthy: projected conversion (${projected}) is comfortably above the ${breakEven} break-even threshold.`;
});

// Clarifies incremental orders vs. customers who would have ordered anyway.
const ordersContextMessage = computed(() => {
  if (!activeScenario.value) return '';
  const { baseline_orders, campaign_orders } = activeScenario.value.result;
  return `${formatInteger(baseline_orders)} would order anyway · ${formatInteger(campaign_orders)} total with this campaign`;
});

const isCustomScenario = computed(() => props.selectedScenario === 'custom');
const isCustomModalOpen = ref(false);

function handleSelectScenario(type: ScenarioType | null) {
  if (type === 'custom') {
    isCustomModalOpen.value = true;
    emit('select-scenario', 'custom');
  } else {
    emit('select-scenario', type);
  }
}

function handleCloseCustomModal() {
  isCustomModalOpen.value = false;
}


const estimatedConversionRate = computed(() => {
  if (activeScenario.value) {
    return activeScenario.value.campaign_conversion_rate;
  }

  if (props.assumptions) {
    return props.assumptions.historical_conversion_rate;
  }

  return props.state.customConversionRate ?? scenarios.value.find((scenario) => scenario.type === 'conservative')?.campaign_conversion_rate ?? null;
});

const summaryResponseRate = computed(() => {
  const rate = estimatedConversionRate.value;
  const historical = props.assumptions?.historical_conversion_rate;

  if (rate === null) {
    return '—';
  }

  if (historical !== undefined && historical !== rate) {
    return `${formatPercentage(rate)} (historical: ${formatPercentage(historical)})`;
  }

  return formatPercentage(rate);
});

const scenarioCards = computed(() => scenarios.value.map((scenario) => ({
  ...scenario,
  label: scenario.type === 'strong_response' ? 'Strong response' : scenario.type.charAt(0).toUpperCase() + scenario.type.slice(1),
})));

function formatValue(value: number | null): string {
  return value === null ? '—' : formatCurrency(value, currency.value);
}

function formatOrders(value: number | null): string {
  return value === null ? '—' : formatInteger(value);
}

function setAudienceSize(event: Event) {
  emit('update:audience', Number((event.target as HTMLInputElement).value));
}

function setFixedCampaignCost(event: Event) {
  emit('update:cost', Number((event.target as HTMLInputElement).value));
}


function setDiscountPercentage(event: Event) {
  emit('update:discount', Number((event.target as HTMLInputElement).value));
}

function setPointsMultiplier(event: Event) {
  emit('update:multiplier', Number((event.target as HTMLInputElement).value));
}

function setCustomConversionRate(event: Event) {
  const value = (event.target as HTMLInputElement).value;
  emit('update:custom', value === '' ? null : Number(value));
}
</script>

<template>
  <main class="app-shell">
    <header class="wizard-header">
      <div>
        <p class="eyebrow">Kangaroo Campaign Advisor</p>
        <h1>Campaign outcomes for merchants</h1>
        <p class="hero-copy">
          This demo simulates two merchants with different buying patterns and uses their historical data to review the next campaign before launch.
        </p>
      </div>
      <ol class="stepper">
        <li :class="{ 'is-active': props.state.step === 'merchant' }">Merchant</li>
        <li :class="{ 'is-active': props.state.step === 'campaign' }">Campaign</li>
        <li :class="{ 'is-active': props.state.step === 'analysis' }">Analysis</li>
        <li :class="{ 'is-active': props.state.step === 'review' }">Review</li>
      </ol>
    </header>

    <section v-if="props.state.step === 'merchant'" class="step-card">
      <div class="step-intro">
        <p class="eyebrow">Step 1</p>
        <h2>Choose a merchant account</h2>
        <p>Pick the merchant whose historical activity will shape the recommendation.</p>
      </div>
      <div class="choice-grid">
        <button class="choice-card" type="button" @click="emit('update:merchant', 101); emit('go-to-step', 'campaign')">
          <span class="choice-kicker">Merchant 101</span>
          <strong>Atelier Nord Cafe</strong>
          <small>Balanced margins and steady repeat purchases</small>
        </button>
        <button class="choice-card" type="button" @click="emit('update:merchant', 202); emit('go-to-step', 'campaign')">
          <span class="choice-kicker">Merchant 202</span>
          <strong>Saint-Paul Market</strong>
          <small>Lower margin, but stronger points activity</small>
        </button>
      </div>
    </section>

    <section v-else-if="props.state.step === 'campaign'" class="step-card">
      <div class="step-intro">
        <p class="eyebrow">Step 2</p>
        <h2>Review the campaign draft</h2>
        <p>This screen matches the current Kangaroo flow: the merchant already chose the campaign parameters, and the advisor only reviews the financial impact.</p>
      </div>

      <div class="draft-grid">
        <div class="field-group">
          <label class="field">
            <span>Audience size</span>
            <input type="number" min="1" step="1" :value="props.state.audienceSize" @input="setAudienceSize">
          </label>
          <label class="field">
            <span>Fixed campaign cost ({{ currency }})</span>
            <input type="number" min="0" step="0.01" :value="props.state.fixedCampaignCost" @input="setFixedCampaignCost">
          </label>
          <div class="field field--choice">
            <span>Campaign type</span>
            <div class="segmented-control" role="tablist" aria-label="Campaign type">
              <button
                type="button"
                class="segmented-control__option"
                :class="{ 'is-active': props.state.campaignType === 'percentage_discount' }"
                @click="emit('update:type', 'percentage_discount')"
              >
                <span class="segmented-control__icon">%</span>
                <span class="segmented-control__text">
                  <strong>Percentage discount</strong>
                  <small>Simple and direct incentive</small>
                </span>
              </button>
              <button
                type="button"
                class="segmented-control__option"
                :class="{ 'is-active': props.state.campaignType === 'double_points' }"
                @click="emit('update:type', 'double_points')"
              >
                <span class="segmented-control__icon">2×</span>
                <span class="segmented-control__text">
                  <strong>Double points</strong>
                  <small>Reward loyalty without a price cut</small>
                </span>
              </button>
              <button type="button" class="segmented-control__option segmented-control__option--muted" disabled>
                <span class="segmented-control__icon">$</span>
                <span class="segmented-control__text">
                  <strong>Fixed amount</strong>
                  <small>Coming soon</small>
                </span>
              </button>
              <button type="button" class="segmented-control__option segmented-control__option--muted" disabled>
                <span class="segmented-control__icon">+</span>
                <span class="segmented-control__text">
                  <strong>Buy one get one</strong>
                  <small>Coming soon</small>
                </span>
              </button>
            </div>
            <small class="field-hint">More campaign types are coming soon.</small>
          </div>
        </div>

        <div class="field-group">
          <label v-if="props.state.campaignType === 'percentage_discount'" class="field">
            <span>Discount percentage</span>
            <input type="number" min="0" max="100" step="0.01" :value="props.state.discountPercentage" @input="setDiscountPercentage">
          </label>
          <label v-else class="field">
            <span>Points multiplier</span>
            <input type="number" min="1" max="10" step="0.1" :value="props.state.pointsMultiplier" @input="setPointsMultiplier">
          </label>
          <label class="field">
            <span>Estimated audience response (optional)</span>
            <input
              type="number"
              min="0"
              max="100"
              step="0.01"
              :value="props.state.customConversionRate ?? ''"
              @input="setCustomConversionRate"
            >
            <small class="field-hint">This is the total campaign conversion rate. The Campaign Advisor uses the merchant's historical data to estimate the incremental orders from that response.</small>
          </label>
          <div class="hint-card">
            <strong>{{ campaignName }}: </strong>
            <span>
              {{ props.state.campaignType === 'percentage_discount'
                ? `A discount campaign targeting ${formatInteger(props.state.audienceSize)} people with an estimated response of ${summaryResponseRate}.`
                : `A double-points campaign targeting ${formatInteger(props.state.audienceSize)} people with an estimated response of ${summaryResponseRate}.`
              }}
            </span>
          </div>
        </div>
      </div>

      <div class="actions-row">
        <button class="secondary-button" type="button" @click="emit('go-to-step', 'merchant')">Back</button>
        <button class="primary-button" type="button" :disabled="props.state.loading" @click="emit('analyze')">
          {{ props.state.loading ? 'Starting…' : 'Start campaign analysis' }}
        </button>
      </div>
    </section>

    <section v-else-if="props.state.step === 'analysis'" class="step-card">
      <div class="step-intro">
        <p class="eyebrow">Step 3</p>
        <h2>Compare scenarios and decide</h2>
        <p>Compare realistic outcomes and choose the safest launch condition.</p>
      </div>

      <div class="segmented-control segmented-control--scenarios" role="tablist" aria-label="Scenarios">
        <button
          v-for="scenario in scenarioCards.filter((scenario) => scenario.type !== 'custom')"
          :key="scenario.type"
          type="button"
          class="segmented-control__option scenario-option"
          :class="{ 'is-active': scenario.type === selectedScenario }"
          @click="emit('select-scenario', scenario.type)"
        >
          <span class="segmented-control__text">
            <strong>{{ scenario.label }}</strong>
            <small>{{ formatPercentage(scenario.campaign_conversion_rate) }}</small>
          </span>
          <span class="segmented-control__value">{{ formatValue(scenario.result.net_impact) }}</span>
        </button>

        <button
          type="button"
          class="segmented-control__option scenario-option scenario-option--custom"
          :class="{ 'is-active': isCustomScenario }"
          @click="handleSelectScenario('custom')"
        >
          <span class="segmented-control__text">
            <strong>Custom</strong>
            <small>{{ props.state.customConversionRate === null ? 'Enter a rate' : formatPercentage(props.state.customConversionRate) }}</small>
          </span>
          <span class="segmented-control__value">{{ customScenario ? formatValue(customScenario.result.net_impact) : '✎' }}</span>
        </button>
      </div>

      <div v-if="isCustomModalOpen" class="custom-dialog-backdrop" role="presentation">
        <div class="custom-dialog" role="dialog" aria-modal="true" aria-label="Custom scenario">
          <div class="custom-dialog__head">
            <div>
              <p class="eyebrow">Custom scenario</p>
              <h3>Test a different audience response</h3>
            </div>
            <button type="button" class="custom-dialog__close" @click="handleCloseCustomModal">×</button>
          </div>

          <label class="field">
            <span>Total campaign conversion rate</span>
            <input
              type="number"
              min="0"
              max="100"
              step="0.01"
              class="custom-dialog__input"
              :value="props.state.customConversionRate ?? ''"
              @input="setCustomConversionRate"
              placeholder="Enter a custom conversion"
            >
            <small>This is the total campaign response for the scenario. The backend calculates the incremental orders from the merchant's historical data.</small>
          </label>

          <div class="custom-dialog__actions">
            <button type="button" class="secondary-button" @click="emit('select-scenario', 'expected')">Cancel</button>
            <button
              type="button"
              class="primary-button"
              @click="async () => {
                              await emit('apply-custom');
                              emit('select-scenario', 'custom');
                              handleCloseCustomModal();
                            }"
            >
                Apply scenario
            </button>
          </div>
        </div>
      </div>

      <p class="field-hint">
        Each percentage represents the total campaign response for that scenario. Conservative uses 50% of the historical lift, expected uses 100%, strong response uses 150%, and custom lets the merchant test a different audience response.
      </p>

      <transition name="fade" mode="out-in">
        <article v-if="activeScenario" :key="activeScenario.type" class="summary-card summary-card--full" :class="`is-${activeTone}`">
          <div class="summary-card__head">
            <span class="summary-label">{{ activeHealthLabel }}</span>
            <span class="summary-tag">Scenario result</span>
          </div>

          <h2>{{ formatValue(activeScenario.result.net_impact) }}</h2>
          <p class="summary-subtitle">Estimated profit or loss if this scenario plays out as projected</p>

          <p class="summary-driver">{{ driverMessage }}</p>

          <div class="summary-stats">
            <div class="summary-stat">
              <span>Incremental orders</span>
              <strong>{{ formatOrders(activeScenario.result.incremental_orders) }}</strong>
              <small>{{ ordersContextMessage }}</small>
            </div>
            <div class="summary-stat">
              <span>Orders needed to break even</span>
              <strong>{{ formatOrders(breakEvenIncrementalOrders) }} incremental</strong>
              <small>{{ formatPercentage(activeScenario.result.break_even_conversion_rate) }} of audience total</small>
            </div>
          </div>

          <div class="progress-track" aria-label="Break-even progress">
            <div class="progress-fill" :style="{ width: `${breakEvenProgress}%` }" />
          </div>
          <p class="hint">
            Projected conversion {{ formatPercentage(activeScenario.campaign_conversion_rate) }} vs
            break-even {{ formatPercentage(activeScenario.result.break_even_conversion_rate) }}
          </p>
        </article>
      </transition>

      <details class="accordion">
        <summary class="accordion__summary">
          <span class="eyebrow">Merchant assumptions</span>
          <h3>{{ merchant?.name ?? 'Merchant profile' }}</h3>
        </summary>

        <div class="accordion__body">
          <div class="metrics-grid">
            <div class="metric-card">
              <span>Average order value</span>
              <strong>{{ assumptions ? formatValue(assumptions.average_order_value) : '—' }}</strong>
            </div>
            <div class="metric-card">
              <span>Gross margin</span>
              <strong>{{ assumptions ? formatPercentage(assumptions.gross_margin_percentage) : '—' }}</strong>
            </div>
            <div class="metric-card">
              <span>Historical conversion</span>
              <strong>{{ assumptions ? formatPercentage(assumptions.historical_conversion_rate) : '—' }}</strong>
            </div>
            <div class="metric-card">
              <span>Historical lift</span>
              <strong>{{ assumptions ? formatPercentage(assumptions.historical_campaign_lift_percentage) : '—' }}</strong>
            </div>
          </div>

          <p class="hint-card">
            Historical data is used to estimate the incremental share of the campaign response. The field above shows total campaign conversion; the incremental orders are derived from the merchant's baseline activity.
          </p>
        </div>
      </details>

      <div class="insight-grid">
        <div class="insight-card">
          <span>Incremental revenue</span>
          <strong>{{ activeScenario ? formatValue(activeScenario.result.incremental_revenue) : '—' }}</strong>
        </div>
        <div class="insight-card">
          <span>Incentive cost</span>
          <strong>{{ activeScenario ? formatValue(activeScenario.result.incentive_cost) : '—' }}</strong>
        </div>
        <div class="insight-card">
          <span>ROI</span>
          <strong>{{ activeScenario && activeScenario.result.roi !== null ? `${activeScenario.result.roi.toFixed(2)}%` : '—' }}</strong>
        </div>
        <div class="insight-card">
          <span>Break-even conversion</span>
          <strong>{{ activeScenario ? formatPercentage(activeScenario.result.break_even_conversion_rate) : '—' }}</strong>
        </div>
      </div>

      <div class="panel-card">
        <div class="compare-grid">
          <div class="compare-card">
            <span>Historical conversion</span>
            <strong>{{ assumptions ? formatPercentage(assumptions.historical_conversion_rate) : '—' }}</strong>
          </div>
          <div class="compare-card compare-card--target">
            <span>Break-even conversion</span>
            <strong>{{ activeScenario ? formatPercentage(activeScenario.result.break_even_conversion_rate) : '—' }}</strong>
          </div>
        </div>
        <div class="progress-track progress-track--compare">
          <div class="progress-fill" :style="{ width: `${breakEvenProgress}%` }" />
        </div>
      </div>

      <p v-if="props.state.error" class="error-banner">{{ props.state.error }}</p>

      <div class="actions-row">
        <button class="secondary-button" type="button" @click="emit('go-to-step', 'campaign')">Edit draft</button>
        <button class="primary-button" type="button" @click="emit('go-to-step', 'review')">Continue to review</button>
      </div>
    </section>

    <section v-else class="step-card">
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
            <li><span>Audience</span><strong>{{ formatInteger(props.state.audienceSize) }}</strong></li>
            <li><span>Fixed cost</span><strong>{{ formatCurrency(props.state.fixedCampaignCost, currency) }}</strong></li>
            <li><span>Campaign type</span><strong>{{ props.state.campaignType }}</strong></li>
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
            <li><span>Health</span><strong>{{ activeHealthLabel }}</strong></li>
            <li><span>Net impact</span><strong>{{ activeScenario ? formatValue(activeScenario.result.net_impact) : '—' }}</strong></li>
            <li><span>Break-even</span><strong>{{ activeScenario ? formatPercentage(activeScenario.result.break_even_conversion_rate) : '—' }}</strong></li>
            <li><span>ROI</span><strong>{{ activeScenario && activeScenario.result.roi !== null ? `${activeScenario.result.roi.toFixed(2)}%` : '—' }}</strong></li>
          </ul>
        </article>
      </div>

      <div class="actions-row">
        <button class="secondary-button" type="button" @click="emit('go-to-step', 'analysis')">Back to analysis</button>
        <button class="secondary-button" type="button" @click="emit('reset')">Start over</button>
      </div>
    </section>
  </main>
</template>
