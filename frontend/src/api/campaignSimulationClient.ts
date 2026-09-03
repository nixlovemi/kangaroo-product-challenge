import { API_BASE_URL, API_KEY } from '../config/app';
import type { ApiEnvelope, ScenarioAnalysisData, SimulationRequestBody } from '../types/campaign';

export class CampaignSimulationApiError extends Error {
  public readonly errors: Record<string, string[]>;

  public constructor(message: string, errors: Record<string, string[]> = {}) {
    super(message);
    this.name = 'CampaignSimulationApiError';
    this.errors = errors;
  }
}

export class CampaignSimulationClient {
  private readonly maxAttempts = 2;

  public async getScenarioAnalysis(body: SimulationRequestBody): Promise<ScenarioAnalysisData> {
    let lastError: unknown = null;

    for (let attempt = 1; attempt <= this.maxAttempts; attempt++) {
      try {
        return await this.fetchScenarioAnalysis(body);
      } catch (error) {
        lastError = error;

        if (attempt < this.maxAttempts && this.isRetryable(error)) {
          await this.delay(250);
          continue;
        }
      }
    }

    throw lastError instanceof Error
      ? lastError
      : new CampaignSimulationApiError('The campaign could not be analyzed.');
  }

  private async fetchScenarioAnalysis(body: SimulationRequestBody): Promise<ScenarioAnalysisData> {
    const response = await fetch(`${API_BASE_URL}/campaigns/simulate/scenarios`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-API-Key': API_KEY,
      },
      body: JSON.stringify(body),
    });

    const payload = (await response.json()) as ApiEnvelope<ScenarioAnalysisData>;

    if (!response.ok || !payload.success || !payload.data) {
      throw new CampaignSimulationApiError(payload.message, payload.errors);
    }

    return payload.data;
  }

  private isRetryable(error: unknown): boolean {
    return error instanceof TypeError;
  }

  private delay(milliseconds: number): Promise<void> {
    return new Promise((resolve) => setTimeout(resolve, milliseconds));
  }
}
