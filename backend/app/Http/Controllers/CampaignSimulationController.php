<?php

namespace App\Http\Controllers;

use App\Domain\Campaigns\Exceptions\MerchantProfileNotFoundException;
use App\Domain\Campaigns\Services\CampaignSimulationService;
use App\Http\Requests\SimulationRequest;
use App\Http\Resources\CampaignScenarioAnalysisResource;
use App\Http\Resources\SimulationResultResource;
use Illuminate\Http\JsonResponse;

final class CampaignSimulationController extends Controller
{
    public function __construct(
        private readonly CampaignSimulationService $simulationService,
    ) {
    }

    public function simulate(SimulationRequest $request): SimulationResultResource|JsonResponse
    {
        try {
            return new SimulationResultResource(
                $this->simulationService->simulateForMerchant($request->toCampaignDraft()),
            );
        } catch (MerchantProfileNotFoundException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }
    }

    public function scenarios(SimulationRequest $request): CampaignScenarioAnalysisResource|JsonResponse
    {
        try {
            return new CampaignScenarioAnalysisResource(
                $this->simulationService->simulateScenariosForMerchant($request->toCampaignDraft()),
            );
        } catch (MerchantProfileNotFoundException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }
    }
}
