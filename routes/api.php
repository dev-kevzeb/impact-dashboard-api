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
use App\Modules\Program\Controller\ProgramController;
use App\Modules\Sdg\Controller\SdgController;
use App\Modules\ProjectState\Controller\ProjectStateController;
use App\Modules\Contact\Controller\ContactController;
use App\Modules\Agency\Controller\AgencyController;
use App\Modules\CountryKpa\Controller\CountryKpaController;
use \App\Modules\StrategicOutput\Controller\StrategicOutputController;
use App\Modules\Project\Controller\ProjectController;
use App\Modules\ProjectAgency\Controller\ProjectAgencyController;
use App\Modules\ProjectIndicator\Controller\ProjectIndicatorController;
use App\Modules\Role\Controller\RoleController;
use App\Modules\UserState\Controller\UserStateController;



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

    // API Routes para Roles
    Route::get('roles', [RoleController::class, 'index']);
    Route::post('roles', [RoleController::class, 'store']);
    Route::get('roles/search', [RoleController::class, 'search']);
    Route::get('roles/{id}', [RoleController::class, 'show']);
    Route::put('roles/{id}', [RoleController::class, 'update']);

    // API Routes para UserState 
    Route::get('user_states', [UserStateController::class, 'index']);
    Route::post('user_states', [UserStateController::class, 'store']);
    Route::get('user_states/search', [UserStateController::class, 'search']);
    Route::get('user_states/{id}', [UserStateController::class, 'show']);
    Route::put('user_states/{id}', [UserStateController::class, 'update']);

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
    Route::post('measures-indicators', [MeasureController::class, 'addIndicator']);
    Route::get('measures-indicators/{id}', [MeasureController::class, 'showWithIndicators']);
    Route::get('measures/{id}/indicators/{name}', [MeasureController::class, 'getIndicatorByName']);
    Route::post('measures/remove-indicator', [MeasureController::class, 'removeIndicator']);

    // API Routes para Strategic Outputs
    Route::get('strategic-outputs', [StrategicOutputController::class, 'index']);
    Route::post('strategic-outputs', [StrategicOutputController::class, 'store']);
    Route::get('strategic-outputs/search', [StrategicOutputController::class, 'search']);
    Route::get('strategic-outputs/{id}', [StrategicOutputController::class, 'show']);
    Route::put('strategic-outputs/{id}', [StrategicOutputController::class, 'update']);
    Route::post('strategic-outputs-measures', [StrategicOutputController::class, 'addMeasure']);
    Route::post('strategic-outputs/remove-measure', [StrategicOutputController::class, 'removeMeasure']);

    // API Routes para Programs
    Route::get('programs', [ProgramController::class, 'index']);
    Route::post('programs', [ProgramController::class, 'store']);
    Route::get('programs/search', [ProgramController::class, 'search']);
    Route::get('programs/{id}', [ProgramController::class, 'show']);
    Route::put('programs/{id}', [ProgramController::class, 'update']);

    // API Routes para Project
    Route::get('projects', [ProjectController::class, 'index']);
    Route::post('projects', [ProjectController::class, 'store']);
    Route::get('projects/search', [ProjectController::class, 'search']);
    Route::get('projects/{id}', [ProjectController::class, 'show']);
    Route::put('projects/{id}', [ProjectController::class, 'update']);

    // Listar todas las relaciones proyecto-agencia
    Route::get('project-agencies', [ProjectAgencyController::class,'index']);
    Route::post('project-agencies', [ProjectAgencyController::class,'createProjectAgency']);
    Route::delete('project-agencies', [ProjectAgencyController::class,'deleteProjectAgency']);
    Route::get('project-agencies/projects/by-agency/{id}', [ProjectAgencyController::class,'showProjectsByAgencyId']);
    Route::get('project-agencies/projects/by-agency-name/{name}', [ProjectAgencyController::class, 'showProjectsByAgencyName']);
    Route::get('project-agencies/agencies/by-project/{id}', [ProjectAgencyController::class,'showAgenciesByProjectId']);
    Route::get('project-agencies/agencies/by-project-name/{name}', [ProjectAgencyController::class,'showAgenciesByProjectName']);
  
    // Listar todas las relaciones proyecto-indicador
    Route::get('project-indicators', [ProjectIndicatorController::class,'index']);
    Route::post('project-indicators', [ProjectIndicatorController::class,'createProjectIndicator']);
    Route::delete('project-indicators', [ProjectIndicatorController::class,'deleteProjectIndicator']);
        
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
    Route::get('country-kpas/country/{id}', [CountryKpaController::class, 'showForCountry']);
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
    Route::get('measures/strategic-output/{id}', [MeasureController::class,'listByStrategicOutput']);
    Route::post('measures-indicators', [MeasureController::class, 'addIndicator']);
    Route::get('measures-indicators/{id}', [MeasureController::class, 'showWithIndicators']);
    Route::get('measures/{id}/indicators/{name}', [MeasureController::class, 'getIndicatorByName']);
    Route::post('measures/remove-indicator', [MeasureController::class, 'removeIndicator']);

    // API Routes para Strategic Outputs
    Route::get('strategic-outputs', [StrategicOutputController::class, 'index']);
    Route::post('strategic-outputs', [StrategicOutputController::class, 'store']);
    Route::get('strategic-outputs/search', [StrategicOutputController::class, 'search']);
    Route::get('strategic-outputs/{id}', [StrategicOutputController::class, 'show']);
    Route::put('strategic-outputs/{id}', [StrategicOutputController::class, 'update']);
    Route::get('strategic-outputs/country-kpa/{id}', [StrategicOutputController::class,'showByCountryKpa']);
    Route::post('strategic-outputs-measures', [StrategicOutputController::class, 'addMeasure']);
    Route::post('strategic-outputs/remove-measure', [StrategicOutputController::class, 'removeMeasure']);

    // API Routes para Project
    Route::get('projects', [ProjectController::class, 'index']);
    Route::post('projects', [ProjectController::class, 'store']);
    Route::get('projects/search', [ProjectController::class, 'search']);
    Route::get('projects/{id}', [ProjectController::class, 'show']);
    Route::put('projects/{id}', [ProjectController::class, 'update']);

    // Listar todas las relaciones proyecto-agencia
    Route::get('project-agencies', [ProjectAgencyController::class,'index']);
    Route::post('project-agencies', [ProjectAgencyController::class,'createProjectAgency']);
    Route::delete('project-agencies', [ProjectAgencyController::class,'deleteProjectAgency']);
       
    Route::get('project-agencies/projects/by-agency/{id}', [ProjectAgencyController::class,'showProjectsByAgencyId']);
    Route::get('project-agencies/projects/by-agency-name/{name}', [ProjectAgencyController::class, 'showProjectsByAgencyName']);
        
    Route::get('project-agencies/agencies/by-project/{id}', [ProjectAgencyController::class,'showAgenciesByProjectId']);
    Route::get('project-agencies/agencies/by-project-name/{name}', [ProjectAgencyController::class,'showAgenciesByProjectName']);
    
    // Listar todas las relaciones proyecto-indicador
    Route::get('project-indicators', [ProjectIndicatorController::class,'index']);
    Route::post('project-indicators', [ProjectIndicatorController::class,'createProjectIndicator']);
    Route::delete('project-indicators', [ProjectIndicatorController::class,'deleteProjectIndicator']);
        
    Route::get('project-indicators/projects/by-indicator/{id}', [ProjectIndicatorController::class,'showProjectsByIndicatorId']);
    Route::get('project-indicators/projects/by-indicator/{id}', [ProjectIndicatorController::class,'showProjectsByIndicatorId']);

    Route::get('project-indicators/indicators/by-project/{id}', [ProjectIndicatorController::class,'showIndicatorsByProjectId']);
    Route::get('project-indicators/indicators/by-project-name/{name}', [ProjectIndicatorController::class,'showIndicatorsByProjectName']);


});
