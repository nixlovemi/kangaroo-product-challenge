<?php

namespace App\Http\Controllers;

use App\Domain\Campaigns\Exceptions\MerchantProfileNotFoundException;
use App\Domain\Campaigns\Services\CampaignSimulationService;
use App\Http\Resources\MerchantOverviewResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MerchantProfileController extends Controller
{
    public function __construct(
        private readonly CampaignSimulationService $simulationService,
    ) {
    }

    public function show(Request $request, int $merchant): JsonResponse
    {
        try {
            $overview = new MerchantOverviewResource(
                $this->simulationService->getMerchantOverview($merchant),
            );

            return $this->successResponse(
                $overview->resolve($request),
                'Merchant profile retrieved.',
            );
        } catch (MerchantProfileNotFoundException $exception) {
            return $this->errorResponse($exception->getMessage(), 404);
        }
    }
}
