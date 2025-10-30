<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Modules\Country\Controller\CountryController;
use App\Modules\Currency\Controller\CurrencyController;
use App\Modules\Kpa\Controller\KpaController;
use App\Modules\Donor\Controller\DonorController;
use App\Modules\Beneficiary\Controller\BeneficiaryController;

Route::prefix('v1')->middleware('api')->group(function () {
    // Currencies explicit routes
    Route::prefix('currencies')->group(function () {
        Route::get('/', [CurrencyController::class, 'index']);
        Route::post('/', [CurrencyController::class, 'store']);
        Route::get('/{id}', [CurrencyController::class, 'show']);
        Route::put('/{id}', [CurrencyController::class, 'update']);
        Route::delete('/{id}', [CurrencyController::class, 'destroy']);
    });

    // Countries explicit routes
    Route::prefix('countries')->group(function () {
        Route::get('/', [CountryController::class, 'index']);
        Route::post('/', [CountryController::class, 'store']);
        Route::get('/{id}', [CountryController::class, 'show']);
        Route::put('/{id}', [CountryController::class, 'update']);
        Route::delete('/{id}', [CountryController::class, 'destroy']);
    });

    // Kpas explicit routes
    Route::prefix('kpas')->group(function () {
        Route::get('/', [KpaController::class, 'index']);
        Route::post('/', [KpaController::class, 'store']);
        Route::get('/{id}', [KpaController::class, 'show']);
        Route::put('/{id}', [KpaController::class, 'update']);
        Route::delete('/{id}', [KpaController::class, 'destroy']);
    });

    // Country-Kpas explicit routes
    Route::prefix('country-kpas')->group(function () {
        Route::get('/', [\App\Modules\CountryKpa\Controller\CountryKpaController::class, 'index']);
        Route::post('/', [\App\Modules\CountryKpa\Controller\CountryKpaController::class, 'store']);
        Route::get('/{id}', [\App\Modules\CountryKpa\Controller\CountryKpaController::class, 'show']);
        Route::delete('/{id}', [\App\Modules\CountryKpa\Controller\CountryKpaController::class, 'destroy']);
    });

    // Contacts explicit routes
    Route::prefix('contacts')->group(function () {
        Route::get('/', [\App\Modules\Contact\Controller\ContactController::class, 'index']);
        Route::post('/', [\App\Modules\Contact\Controller\ContactController::class, 'store']);
        Route::get('/{id}', [\App\Modules\Contact\Controller\ContactController::class, 'show']);
        Route::put('/{id}', [\App\Modules\Contact\Controller\ContactController::class, 'update']);
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
});