<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Modules\Country\Controller\CountryController;
use App\Modules\Currency\Controller\CurrencyController;
use App\Modules\Kpa\Controller\KpaController;
use App\Modules\Donor\Controller\DonorController;
use App\Modules\Beneficiary\Controller\BeneficiaryController;
use App\Modules\ProgramState\Controller\ProgramStateController;


Route::prefix('v1')->middleware('api')->group(function () {
    // Currencies resource (index, show, store, update, destroy)
    Route::apiResource('currencies', CurrencyController::class)->parameters([
        'currencies' => 'id'
    ]);

    Route::apiResource('countries', CountryController::class)->parameters([
        'countries' => 'id'
    ]);

    Route::apiResource('kpas', KpaController::class)->parameters([
        'kpas' => 'id'
    ]);
    Route::apiResource('country-kpas', \App\Modules\CountryKpa\Controller\CountryKpaController::class)->parameters([
        'country-kpas' => 'id'
    ]);
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // API Routes para Donors
    Route::get('donors', [DonorController::class, 'index']);
    Route::post('donors', [DonorController::class, 'store']);
    Route::get('donors/search', [DonorController::class, 'search']);
    Route::get('donors/{id}', [DonorController::class, 'show']);
    Route::put('donors/{id}', [DonorController::class, 'update']);

    // API Routes para Beneficiaries
    Route::get('beneficiaries', [BeneficiaryController::class, 'index']);
    Route::post('beneficiaries', [BeneficiaryController::class, 'store']);
    Route::get('beneficiaries/search', [BeneficiaryController::class, 'search']);
    Route::get('beneficiaries/{id}', [BeneficiaryController::class, 'show']);
    Route::put('beneficiaries/{id}', [BeneficiaryController::class, 'update']);

    // API Routes para ProgramStates
    Route::get('program_states', [ProgramStateController::class, 'index']);
    Route::post('program_states', [ProgramStateController::class, 'store']);
    Route::get('program_states/search', [ProgramStateController::class, 'search']);
    Route::get('program_states/{id}', [ProgramStateController::class, 'show']);
    Route::put('program_states/{id}', [ProgramStateController::class, 'update']);
});