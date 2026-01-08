<?php


use App\Modules\Auth\Controller\AuthController;
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
use App\Modules\CountryKpaUser\Controller\CountryKpaUserController;
use App\Modules\ProgramUser\Controller\ProgramUserController;
use App\Modules\UserRole\Controller\UserRoleController;
use \App\Modules\StrategicOutput\Controller\StrategicOutputController;
use App\Modules\Project\Controller\ProjectController;
use App\Modules\ProjectAgency\Controller\ProjectAgencyController;
use App\Modules\ProjectIndicator\Controller\ProjectIndicatorController;
use App\Modules\Role\Controller\RoleController;
use App\Modules\UserState\Controller\UserStateController;
use App\Modules\User\Controller\UserController;



Route::prefix('v1')->group(function () {

    // ========================================
    // PUBLIC ROUTES (No authentication)
    // ========================================
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);

    // ========================================
    // PROTECTED ROUTES (JWT authentication required)
    // ========================================
    Route::middleware('jwt')->group(function () {
        
        // Auth endpoints (authenticated users only)
        Route::post('auth/refresh', [AuthController::class, 'refresh']);
        Route::get('auth/me', [AuthController::class, 'me']);

        // Donors - Require 'donors' scope
        Route::get('donors', [DonorController::class, 'index'])->middleware('scope:donors');
        Route::get('donors/search', [DonorController::class, 'search'])->middleware('scope:donors');
        Route::get('donors/{id}', [DonorController::class, 'show'])->middleware('scope:donors');
        Route::post('donors', [DonorController::class, 'store'])->middleware('scope:donors:write');
        Route::put('donors/{id}', [DonorController::class, 'update'])->middleware('scope:donors:write');

        // Beneficiaries - Require 'beneficiaries' scope
        Route::get('beneficiaries', [BeneficiaryController::class, 'index'])->middleware('scope:beneficiaries');
        Route::get('beneficiaries/search', [BeneficiaryController::class, 'search'])->middleware('scope:beneficiaries');
        Route::get('beneficiaries/{id}', [BeneficiaryController::class, 'show'])->middleware('scope:beneficiaries');
        Route::post('beneficiaries', [BeneficiaryController::class, 'store'])->middleware('scope:beneficiaries:write');
        Route::put('beneficiaries/{id}', [BeneficiaryController::class, 'update'])->middleware('scope:beneficiaries:write');

        // Program States - Require 'programs' scope
        Route::get('program_states', [ProgramStateController::class, 'index'])->middleware('scope:programs');
        Route::get('program_states/search', [ProgramStateController::class, 'search'])->middleware('scope:programs');
        Route::get('program_states/{id}', [ProgramStateController::class, 'show'])->middleware('scope:programs');
        Route::post('program_states', [ProgramStateController::class, 'store'])->middleware('scope:programs:write');
        Route::put('program_states/{id}', [ProgramStateController::class, 'update'])->middleware('scope:programs:write');

        // Currencies - Require 'donors' scope (related to donor module)
        Route::get('currencies', [CurrencyController::class, 'index'])->middleware('scope:donors');
        Route::get('currencies/search', [CurrencyController::class, 'search'])->middleware('scope:donors');
        Route::get('currencies/{id}', [CurrencyController::class, 'show'])->middleware('scope:donors');
        Route::post('currencies', [CurrencyController::class, 'store'])->middleware('scope:donors:write');
        Route::put('currencies/{id}', [CurrencyController::class, 'update'])->middleware('scope:donors:write');

        // Countries - Require 'kpas' scope (related to kpa module)
        Route::get('countries', [CountryController::class, 'index'])->middleware('scope:kpas');
        Route::get('countries/search', [CountryController::class, 'search'])->middleware('scope:kpas');
        Route::get('countries/{id}', [CountryController::class, 'show'])->middleware('scope:kpas');
        Route::post('countries', [CountryController::class, 'store'])->middleware('scope:kpas:write');
        Route::put('countries/{id}', [CountryController::class, 'update'])->middleware('scope:kpas:write');

        // KPAs - Require 'kpas' scope
        Route::get('kpas', [KpaController::class, 'index'])->middleware('scope:kpas');
        Route::get('kpas/search', [KpaController::class, 'search'])->middleware('scope:kpas');
        Route::get('kpas/{id}', [KpaController::class, 'show'])->middleware('scope:kpas');
        Route::post('kpas', [KpaController::class, 'store'])->middleware('scope:kpas:write');
        Route::put('kpas/{id}', [KpaController::class, 'update'])->middleware('scope:kpas:write');

        // Users - Require 'users' scope
        Route::get('users', [UserController::class, 'index'])->middleware('scope:users');
        Route::get('users/search', [UserController::class, 'search'])->middleware('scope:users');
        Route::get('users/{id}', [UserController::class, 'show'])->middleware('scope:users');
        Route::post('users', [UserController::class, 'store'])->middleware('scope:users:write');
        Route::put('users/{id}', [UserController::class, 'update'])->middleware('scope:users:write');

        // Roles - Require 'users' scope (admin functionality)
        Route::get('roles', [RoleController::class, 'index'])->middleware('scope:users');
        Route::get('roles/search', [RoleController::class, 'search'])->middleware('scope:users');
        Route::get('roles/{id}', [RoleController::class, 'show'])->middleware('scope:users');
        Route::post('roles', [RoleController::class, 'store'])->middleware('scope:users:write');
        Route::put('roles/{id}', [RoleController::class, 'update'])->middleware('scope:users:write');

        // User States - Require 'users' scope
        Route::get('user_states', [UserStateController::class, 'index'])->middleware('scope:users');
        Route::get('user_states/search', [UserStateController::class, 'search'])->middleware('scope:users');
        Route::get('user_states/{id}', [UserStateController::class, 'show'])->middleware('scope:users');
        Route::post('user_states', [UserStateController::class, 'store'])->middleware('scope:users:write');
        Route::put('user_states/{id}', [UserStateController::class, 'update'])->middleware('scope:users:write');

        // Country-KPA Users - Require 'kpas' scope
        Route::get('country_kpa_users', [CountryKpaUserController::class, 'index'])->middleware('scope:kpas');
        Route::get('country_kpa_users/{id}', [CountryKpaUserController::class, 'show'])->middleware('scope:kpas');
        Route::post('country_kpa_users', [CountryKpaUserController::class, 'store'])->middleware('scope:kpas:write');
        Route::put('country_kpa_users/{id}', [CountryKpaUserController::class, 'update'])->middleware('scope:kpas:write');
        Route::delete('country_kpa_users/{id}', [CountryKpaUserController::class, 'destroy'])->middleware('scope:kpas:write');

        // User Roles - Require 'users' scope
        Route::get('user_roles', [UserRoleController::class, 'index'])->middleware('scope:users');
        Route::get('user_roles/{id}', [UserRoleController::class, 'show'])->middleware('scope:users');
        Route::post('user_roles', [UserRoleController::class, 'store'])->middleware('scope:users:write');
        Route::put('user_roles/{id}', [UserRoleController::class, 'update'])->middleware('scope:users:write');
        Route::delete('user_roles/{id}', [UserRoleController::class, 'destroy'])->middleware('scope:users:write');

        // Program Users - Require 'programs' scope
        Route::get('program_users', [ProgramUserController::class, 'index'])->middleware('scope:programs');
        Route::get('program_users/{id}', [ProgramUserController::class, 'show'])->middleware('scope:programs');
        Route::post('program_users', [ProgramUserController::class, 'store'])->middleware('scope:programs:write');
        Route::put('program_users/{id}', [ProgramUserController::class, 'update'])->middleware('scope:programs:write');
        Route::delete('program_users/{id}', [ProgramUserController::class, 'destroy'])->middleware('scope:programs:write');

        // Country-KPAs - Require 'kpas' scope
        Route::get('country-kpas', [CountryKpaController::class, 'index'])->middleware('scope:kpas');
        Route::get('country-kpas/{id}', [CountryKpaController::class, 'show'])->middleware('scope:kpas');
        Route::get('country-kpas/country/{id}', [CountryKpaController::class, 'showForCountry'])->middleware('scope:kpas');
        Route::post('country-kpas', [CountryKpaController::class, 'store'])->middleware('scope:kpas:write');
        Route::put('country-kpas/{id}', [CountryKpaController::class, 'update'])->middleware('scope:kpas:write');
        Route::delete('country-kpas/{id}', [CountryKpaController::class, 'destroy'])->middleware('scope:kpas:write');

        // Contacts - Require 'donors' scope
        Route::get('contacts', [ContactController::class, 'index'])->middleware('scope:donors');
        Route::get('contacts/search', [ContactController::class, 'search'])->middleware('scope:donors');
        Route::get('contacts/{id}', [ContactController::class, 'show'])->middleware('scope:donors');
        Route::post('contacts', [ContactController::class, 'store'])->middleware('scope:donors:write');
        Route::put('contacts/{id}', [ContactController::class, 'update'])->middleware('scope:donors:write');

        // Project States - Require 'projects' scope
        Route::get('project_states', [ProjectStateController::class, 'index'])->middleware('scope:projects');
        Route::get('project_states/{id}', [ProjectStateController::class, 'show'])->middleware('scope:projects');
        Route::post('project_states', [ProjectStateController::class, 'store'])->middleware('scope:projects:write');
        Route::put('project_states/{id}', [ProjectStateController::class, 'update'])->middleware('scope:projects:write');

        // SDGs - Require 'projects' scope
        Route::get('sdgs', [SdgController::class, 'index'])->middleware('scope:projects');
        Route::get('sdgs/search', [SdgController::class, 'search'])->middleware('scope:projects');
        Route::get('sdgs/{id}', [SdgController::class, 'show'])->middleware('scope:projects');
        Route::post('sdgs', [SdgController::class, 'store'])->middleware('scope:projects:write');
        Route::put('sdgs/{id}', [SdgController::class, 'update'])->middleware('scope:projects:write');

        // Agencies - Require 'projects' scope
        Route::get('agencies', [AgencyController::class, 'index'])->middleware('scope:projects');
        Route::get('agencies/search', [AgencyController::class, 'search'])->middleware('scope:projects');
        Route::get('agencies/{id}', [AgencyController::class, 'show'])->middleware('scope:projects');
        Route::post('agencies', [AgencyController::class, 'store'])->middleware('scope:projects:write');
        Route::put('agencies/{id}', [AgencyController::class, 'update'])->middleware('scope:projects:write');

        // Indicator Types - Require 'projects' scope
        Route::get('indicator-types', [IndicatorTypeController::class, 'index'])->middleware('scope:projects');
        Route::get('indicator-types/search', [IndicatorTypeController::class, 'search'])->middleware('scope:projects');
        Route::get('indicator-types/{id}', [IndicatorTypeController::class, 'show'])->middleware('scope:projects');
        Route::post('indicator-types', [IndicatorTypeController::class, 'store'])->middleware('scope:projects:write');
        Route::put('indicator-types/{id}', [IndicatorTypeController::class, 'update'])->middleware('scope:projects:write');

        // Indicators - Require 'projects' scope
        Route::get('indicators', [IndicatorController::class, 'index'])->middleware('scope:projects');
        Route::get('indicators/search', [IndicatorController::class, 'search'])->middleware('scope:projects');
        Route::get('indicators/{id}', [IndicatorController::class, 'show'])->middleware('scope:projects');
        Route::post('indicators', [IndicatorController::class, 'store'])->middleware('scope:projects:write');
        Route::put('indicators/{id}', [IndicatorController::class, 'update'])->middleware('scope:projects:write');

        // Measures - Require 'projects' scope
        Route::get('measures', [MeasureController::class, 'index'])->middleware('scope:projects');
        Route::get('measures/search', [MeasureController::class, 'search'])->middleware('scope:projects');
        Route::get('measures/{id}', [MeasureController::class, 'show'])->middleware('scope:projects');
        Route::get('measures/strategic-output/{id}', [MeasureController::class, 'listByStrategicOutput'])->middleware('scope:projects');
        Route::get('measures-indicators/{id}', [MeasureController::class, 'showWithIndicators'])->middleware('scope:projects');
        Route::get('measures/{id}/indicators/{name}', [MeasureController::class, 'getIndicatorByName'])->middleware('scope:projects');
        Route::post('measures', [MeasureController::class, 'store'])->middleware('scope:projects:write');
        Route::put('measures/{id}', [MeasureController::class, 'update'])->middleware('scope:projects:write');
        Route::post('measures-indicators', [MeasureController::class, 'addIndicator'])->middleware('scope:projects:write');
        Route::post('measures/remove-indicator', [MeasureController::class, 'removeIndicator'])->middleware('scope:projects:write');

        // Strategic Outputs - Require 'projects' scope
        Route::get('strategic-outputs', [StrategicOutputController::class, 'index'])->middleware('scope:projects');
        Route::get('strategic-outputs/search', [StrategicOutputController::class, 'search'])->middleware('scope:projects');
        Route::get('strategic-outputs/{id}', [StrategicOutputController::class, 'show'])->middleware('scope:projects');
        Route::get('strategic-outputs/country-kpa/{id}', [StrategicOutputController::class, 'showByCountryKpa'])->middleware('scope:projects');
        Route::post('strategic-outputs', [StrategicOutputController::class, 'store'])->middleware('scope:projects:write');
        Route::put('strategic-outputs/{id}', [StrategicOutputController::class, 'update'])->middleware('scope:projects:write');
        Route::post('strategic-outputs-measures', [StrategicOutputController::class, 'addMeasure'])->middleware('scope:projects:write');
        Route::post('strategic-outputs/remove-measure', [StrategicOutputController::class, 'removeMeasure'])->middleware('scope:projects:write');

        // Programs - Require 'programs' scope
        Route::get('programs', [ProgramController::class, 'index'])->middleware('scope:programs');
        Route::get('programs/search', [ProgramController::class, 'search'])->middleware('scope:programs');
        Route::get('programs/{id}', [ProgramController::class, 'show'])->middleware('scope:programs');
        Route::post('programs', [ProgramController::class, 'store'])->middleware('scope:programs:write');
        Route::put('programs/{id}', [ProgramController::class, 'update'])->middleware('scope:programs:write');

        // Projects - Require 'projects' scope
        Route::get('projects', [ProjectController::class, 'index'])->middleware('scope:projects');
        Route::get('projects/search', [ProjectController::class, 'search'])->middleware('scope:projects');
        Route::get('projects/{id}', [ProjectController::class, 'show'])->middleware('scope:projects');
        Route::post('projects', [ProjectController::class, 'store'])->middleware('scope:projects:write');
        Route::put('projects/{id}', [ProjectController::class, 'update'])->middleware('scope:projects:write');

        // Project-Agencies - Require 'projects' scope
        Route::get('project-agencies', [ProjectAgencyController::class, 'index'])->middleware('scope:projects');
        Route::get('project-agencies/projects/by-agency/{id}', [ProjectAgencyController::class, 'showProjectsByAgencyId'])->middleware('scope:projects');
        Route::get('project-agencies/projects/by-agency-name/{name}', [ProjectAgencyController::class, 'showProjectsByAgencyName'])->middleware('scope:projects');
        Route::get('project-agencies/agencies/by-project/{id}', [ProjectAgencyController::class, 'showAgenciesByProjectId'])->middleware('scope:projects');
        Route::get('project-agencies/agencies/by-project-name/{name}', [ProjectAgencyController::class, 'showAgenciesByProjectName'])->middleware('scope:projects');
        Route::post('project-agencies', [ProjectAgencyController::class, 'createProjectAgency'])->middleware('scope:projects:write');
        Route::delete('project-agencies', [ProjectAgencyController::class, 'deleteProjectAgency'])->middleware('scope:projects:write');

        // Project-Indicators - Require 'projects' scope
        Route::get('project-indicators', [ProjectIndicatorController::class, 'index'])->middleware('scope:projects');
        Route::get('project-indicators/projects/by-indicator/{id}', [ProjectIndicatorController::class, 'showProjectsByIndicatorId'])->middleware('scope:projects');
        Route::get('project-indicators/indicators/by-project/{id}', [ProjectIndicatorController::class, 'showIndicatorsByProjectId'])->middleware('scope:projects');
        Route::post('project-indicators', [ProjectIndicatorController::class, 'createProjectIndicator'])->middleware('scope:projects:write');
        Route::delete('project-indicators', [ProjectIndicatorController::class, 'deleteProjectIndicator'])->middleware('scope:projects:write');
    });
});
    Route::get('project-indicators/indicators/by-project-name/{name}', [ProjectIndicatorController::class, 'showIndicatorsByProjectName']);
});
