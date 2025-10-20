<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Modules\Donor\Controller\DonorController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API Routes para Donors
Route::prefix('donors')->group(function () {
    Route::get('/', [DonorController::class, 'index']);
    Route::get('/stats', [DonorController::class, 'stats']);
    Route::get('/search', [DonorController::class, 'search']);
    Route::get('/{id}', [DonorController::class, 'show']);
    Route::post('/', [DonorController::class, 'store']);
    Route::put('/{id}', [DonorController::class, 'update']);
});