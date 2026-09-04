<?php

namespace App\Http\Controllers;

use App\Domain\Campaigns\Exceptions\MerchantProfileNotFoundException;
use App\Domain\Campaigns\Services\CampaignSimulationService;
use App\Domain\Recommendations\Services\CampaignAdvisorService;
use App\Http\Requests\SimulationRequest;
use App\Http\Resources\CampaignAdviceResource;
use App\Http\Resources\SimulationResultResource;
use Illuminate\Http\JsonResponse;

final class CampaignSimulationController extends Controller
{
    public function __construct(
        private readonly CampaignSimulationService $simulationService,
        private readonly CampaignAdvisorService $advisorService,
    ) {
    }

    public function simulate(SimulationRequest $request): JsonResponse
    {
        try {
            $result = new SimulationResultResource(
                $this->simulationService->simulateForMerchant($request->toCampaignDraft()),
            );

            return $this->successResponse(
                $result->resolve($request),
                'Campaign simulation completed.',
            );
        } catch (MerchantProfileNotFoundException $exception) {
            return $this->errorResponse($exception->getMessage(), 404);
        }
    }

    public function scenarios(SimulationRequest $request): JsonResponse
    {
        try {
            $advice = new CampaignAdviceResource(
                $this->advisorService->analyzeScenarios($request->toCampaignDraft()),
            );

            return $this->successResponse(
                $advice->resolve($request),
                'Campaign scenarios calculated.',
            );
        } catch (MerchantProfileNotFoundException $exception) {
            return $this->errorResponse($exception->getMessage(), 404);
        }
    }
}
