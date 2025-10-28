<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Modules\Country\Controller\CountryController;
use App\Modules\Currency\Controller\CurrencyController;
use App\Modules\Kpa\Controller\KpaController;

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


});