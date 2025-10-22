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
});