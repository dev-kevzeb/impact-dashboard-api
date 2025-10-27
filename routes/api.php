<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Modules\Donor\Controller\DonorController;
use App\Modules\Beneficiary\Controller\BeneficiaryController;
use App\Modules\ProgramState\Controller\ProgramStateController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API Routes para Donors
Route::prefix('donors')->group(function () {
    Route::get('/', [DonorController::class, 'index']);
    Route::post('/', [DonorController::class, 'store']);
    Route::get('/search', [DonorController::class, 'search']);        
    Route::get('/{id}', [DonorController::class, 'show']);            
    Route::put('/{id}', [DonorController::class, 'update']);          
});

// API Routes para Beneficiaries
Route::prefix('beneficiaries')->group(function () {
    Route::get('/', [BeneficiaryController::class, 'index']);
    Route::post('/', [BeneficiaryController::class, 'store']);
    Route::get('/search', [BeneficiaryController::class, 'search']);
    Route::get('/{id}', [BeneficiaryController::class, 'show']);            
    Route::put('/{id}', [BeneficiaryController::class, 'update']);
});

// API Routes para ProgramStates
Route::prefix('program_states')->group(function () {
    Route::get('/', [ProgramStateController::class, 'index']);
    Route::post('/', [ProgramStateController::class, 'store']);
    Route::get('/search', [ProgramStateController::class, 'search']);
    Route::get('/{id}', [ProgramStateController::class, 'show']);
    Route::put('/{id}', [ProgramStateController::class, 'update']);
});