<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ReceiverController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/campaign', [CampaignController::class, 'saveCampaign']);

Route::post('/update-status', [ReceiverController::class, 'updateStatus']);

Route::get('/processed-receivers', [ReceiverController::class, 'processedReceivers']);
