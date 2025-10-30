<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Modules\Country\Controller\CountryController;
use App\Modules\Currency\Controller\CurrencyController;
use App\Modules\Kpa\Controller\KpaController;
use App\Modules\Donor\Controller\DonorController;
use App\Modules\Beneficiary\Controller\BeneficiaryController;

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

    Route::apiResource('contacts', \App\Modules\Contact\Controller\ContactController::class)->parameters([
        'contacts' => 'id'
    ]);
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
});