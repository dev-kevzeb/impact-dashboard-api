<?php

use App\Modules\Indicator\Controller\IndicatorController;
use App\Modules\IndicatorType\Controller\IndicatorTypeController;
use App\Modules\Measure\Controller\MeasureController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Modules\Country\Controller\CountryController;
use App\Modules\Currency\Controller\CurrencyController;
use App\Modules\Kpa\Controller\KpaController;
use App\Modules\Donor\Controller\DonorController;
use App\Modules\Beneficiary\Controller\BeneficiaryController;
use App\Modules\ProgramState\Controller\ProgramStateController;

use App\Modules\Sdg\Controller\SdgController;
use App\Modules\ProjectState\Controller\ProjectStateController;

use App\Modules\Contact\Controller\ContactController;
use App\Modules\Agency\Controller\AgencyController;
use App\Modules\CountryKpa\Controller\CountryKpaController;

Route::prefix('v1')->group(function () {
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

    // API Routes para Currencies
    Route::get('currencies', [CurrencyController::class, 'index']);
    Route::post('currencies', [CurrencyController::class, 'store']);
    Route::get('currencies/search', [CurrencyController::class, 'search']);
    Route::get('currencies/{id}', [CurrencyController::class, 'show']);
    Route::put('currencies/{id}', [CurrencyController::class, 'update']);  
    
    // API Routes para Countries
    Route::get('countries', [CountryController::class, 'index']);
    Route::post('countries', [CountryController::class, 'store']);
    Route::get('countries/search', [CountryController::class, 'search']);
    Route::get('countries/{id}', [CountryController::class, 'show']);
    Route::put('countries/{id}', [CountryController::class, 'update']);

    // API Routes para Kpas
    Route::get('kpas', [KpaController::class, 'index']);
    Route::post('kpas', [KpaController::class, 'store']);
    Route::get('kpas/search', [KpaController::class, 'search']);
    Route::get('kpas/{id}', [KpaController::class, 'show']);
    Route::put('kpas/{id}', [KpaController::class, 'update']);

    // API Routes para Country-Kpas
    Route::get('country-kpas', [CountryKpaController::class, 'index']);
    Route::post('country-kpas', [CountryKpaController::class, 'store']);
    Route::get('country-kpas/{id}', [CountryKpaController::class, 'show']);
    Route::put('country-kpas/{id}', [CountryKpaController::class, 'update']);
    Route::delete('country-kpas/{id}', [CountryKpaController::class, 'destroy']);

    // API Routes para Contacts
    Route::get('contacts', [ContactController::class, 'index']);
    Route::post('contacts', [ContactController::class, 'store']);
    Route::get('contacts/search', [ContactController::class, 'search']);
    Route::get('contacts/{id}', [ContactController::class, 'show']);
    Route::put('contacts/{id}', [ContactController::class, 'update']);

    // API Routes para ProjectStates
    Route::get('project_states', [ProjectStateController::class, 'index']);
    Route::post('project_states', [ProjectStateController::class, 'store']);
    Route::get('project_states/{id}', [ProjectStateController::class, 'show']);
    Route::put('project_states/{id}', [ProjectStateController::class, 'update']);
    // API Routes para SDG
    Route::get('sdgs', [SdgController::class, 'index']);
    Route::post('sdgs', [SdgController::class, 'store']);
    Route::get('sdgs/search', [SdgController::class, 'search']);
    Route::get('sdgs/{id}', [SdgController::class, 'show']);
    Route::put('sdgs/{id}', [SdgController::class, 'update']);

    // API routes para Strategic Outputs
    Route::get('strategic-outputs', [\App\Modules\StrategicOutput\Controller\StrategicOutputController::class, 'index']);
    Route::post('strategic-outputs', [\App\Modules\StrategicOutput\Controller\StrategicOutputController::class, 'store']);
    Route::get('strategic-outputs/{id}', [\App\Modules\StrategicOutput\Controller\StrategicOutputController::class, 'show']);
    Route::put('strategic-outputs/{id}', [\App\Modules\StrategicOutput\Controller\StrategicOutputController::class, 'update']);
    // API Routes para Agency
    Route::get('agencies', [AgencyController::class, 'index']);
    Route::post('agencies', [AgencyController::class, 'store']);
    Route::get('agencies/search', [AgencyController::class, 'search']);
    Route::get('agencies/{id}', [AgencyController::class, 'show']);
    Route::put('agencies/{id}', [AgencyController::class, 'update']);

    // API Routes para Indicator Type
    Route::get('indicator-types', [IndicatorTypeController::class, 'index']);
    Route::post('indicator-types', [IndicatorTypeController::class, 'store']);
    Route::get('indicator-types/search', [IndicatorTypeController::class, 'search']);
    Route::get('indicator-types/{id}', [IndicatorTypeController::class, 'show']);
    Route::put('indicator-types/{id}', [IndicatorTypeController::class, 'update']);

    // API Routes para Indicator
    Route::get('indicators', [IndicatorController::class, 'index']);
    Route::post('indicators', [IndicatorController::class, 'store']);
    Route::get('indicators/search', [IndicatorController::class, 'search']);
    Route::get('indicators/{id}', [IndicatorController::class, 'show']);
    Route::put('indicators/{id}', [IndicatorController::class, 'update']);

    // API Routes para Measure
    Route::get('measures', [MeasureController::class, 'index']);
    Route::post('measures', [MeasureController::class, 'store']);
    Route::put('measures/{id}', [MeasureController::class, 'update']);
    Route::get('measures/search', [MeasureController::class, 'search']);
    Route::get('measures/{id}', [MeasureController::class, 'show']);

    Route::get('measures-indicators/{id}', [MeasureController::class, 'showWithIndicators']);
    Route::post('measures-indicators', [MeasureController::class, 'addIndicator']);
    Route::get('measures/{id}/indicators/{name}', [MeasureController::class, 'getIndicatorByName']);

}); 