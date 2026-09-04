import { describe, expect, it } from 'vitest';
import {
  formatCurrency,
  formatInteger,
  formatPercentage,
  formatScenarioType,
  healthStatusLabel,
  healthStatusTone,
} from './campaignFormatters';

describe('campaignFormatters', () => {
  it('formats currency using the configured locale and given currency code', () => {
    expect(formatCurrency(1234.5, 'CAD')).toContain('1,234.50');
    expect(formatCurrency(-129.26, 'CAD')).toContain('129.26');
  });

  it('formats percentages with exactly two decimal places and a trailing % sign', () => {
    expect(formatPercentage(6.5)).toBe('6.50%');
    expect(formatPercentage(0)).toBe('0.00%');
  });

  it('formats integers without decimal places', () => {
    expect(formatInteger(1234.7)).toBe('1,235');
    expect(formatInteger(0)).toBe('0');
  });

  it('maps every scenario type to a human label', () => {
    expect(formatScenarioType('conservative')).toBe('Conservative');
    expect(formatScenarioType('expected')).toBe('Expected');
    expect(formatScenarioType('strong_response')).toBe('Strong response');
    expect(formatScenarioType('custom')).toBe('Custom');
  });

  it('maps every health status to a label and a tone', () => {
    expect(healthStatusLabel('healthy')).toBe('Healthy');
    expect(healthStatusLabel('caution')).toBe('Caution');
    expect(healthStatusLabel('risky')).toBe('Risky');

    expect(healthStatusTone('healthy')).toBe('success');
    expect(healthStatusTone('caution')).toBe('warning');
    expect(healthStatusTone('risky')).toBe('danger');
  });
});

