import { APP_LOCALE } from '../config/app';
import type { HealthStatus, ScenarioType } from '../types/campaign';

const currencyCache = new Map<string, Intl.NumberFormat>();
const percentageFormatter = new Intl.NumberFormat(APP_LOCALE, {
  maximumFractionDigits: 2,
  minimumFractionDigits: 2,
});
const integerFormatter = new Intl.NumberFormat(APP_LOCALE, {
  maximumFractionDigits: 0,
});

export function formatCurrency(value: number, currency: string): string {
  const key = `${APP_LOCALE}:${currency}`;
  if (!currencyCache.has(key)) {
    currencyCache.set(key, new Intl.NumberFormat(APP_LOCALE, {
      style: 'currency',
      currency,
      currencyDisplay: 'symbol',
    }));
  }

  return currencyCache.get(key)!.format(value);
}

export function formatPercentage(value: number): string {
  return `${percentageFormatter.format(value)}%`;
}

export function formatInteger(value: number): string {
  return integerFormatter.format(value);
}

export function formatScenarioType(type: ScenarioType): string {
  const labels: Record<ScenarioType, string> = {
    conservative: 'Conservative',
    expected: 'Expected',
    strong_response: 'Strong response',
    custom: 'Custom',
  };

  return labels[type];
}

export function healthStatusLabel(status: HealthStatus): string {
  const labels: Record<HealthStatus, string> = {
    healthy: 'Healthy',
    caution: 'Caution',
    risky: 'Risky',
  };

  return labels[status];
}

export function healthStatusTone(status: HealthStatus): 'success' | 'warning' | 'danger' {
  const tones: Record<HealthStatus, 'success' | 'warning' | 'danger'> = {
    healthy: 'success',
    caution: 'warning',
    risky: 'danger',
  };

  return tones[status];
}
