import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { logger } from './logger';

describe('logger', () => {
  beforeEach(() => {
    vi.spyOn(console, 'log').mockImplementation(() => {});
    vi.spyOn(console, 'warn').mockImplementation(() => {});
    vi.spyOn(console, 'error').mockImplementation(() => {});
  });

  afterEach(() => {
    vi.restoreAllMocks();
    vi.unstubAllEnvs();
  });

  it('logs debug and warn messages in dev mode', () => {
    vi.stubEnv('DEV', true);

    logger.debug('hello');
    logger.warn('careful');

    expect(console.log).toHaveBeenCalledWith('hello');
    expect(console.warn).toHaveBeenCalledWith('careful');
  });

  it('silences debug and warn messages outside dev mode', () => {
    vi.stubEnv('DEV', false);

    logger.debug('hello');
    logger.warn('careful');

    expect(console.log).not.toHaveBeenCalled();
    expect(console.warn).not.toHaveBeenCalled();
  });

  it('always surfaces errors, even outside dev mode', () => {
    vi.stubEnv('DEV', false);

    logger.error('boom');

    expect(console.error).toHaveBeenCalledWith('boom');
  });
});



