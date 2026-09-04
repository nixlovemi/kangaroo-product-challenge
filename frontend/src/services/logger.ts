/**
 * Thin wrapper around console.* that only logs in dev builds (import.meta.env.DEV).
 * Errors are always surfaced so real production issues are never silently swallowed.
 */
function isDev(): boolean {
  return import.meta.env.DEV;
}

function debug(...args: unknown[]): void {
  if (isDev()) {
    console.log(...args);
  }
}

function warn(...args: unknown[]): void {
  if (isDev()) {
    console.warn(...args);
  }
}

function error(...args: unknown[]): void {
  console.error(...args);
}

export const logger = { debug, warn, error };
