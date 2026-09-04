<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CampaignSimulationController;
use App\Http\Controllers\MerchantProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->middleware(['throttle:60,1', 'api.key'])->group(function () {
    Route::get('merchants/{merchant}/profile', [MerchantProfileController::class, 'show']);
    Route::post('campaigns/simulate', [CampaignSimulationController::class, 'simulate']);
    Route::post('campaigns/simulate/scenarios', [CampaignSimulationController::class, 'scenarios']);
});
