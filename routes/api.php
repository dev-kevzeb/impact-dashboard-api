<?php

use App\Modules\Auth\Controller\AuthController;
use App\Modules\Auth\Controller\VerificationController;
use App\Modules\Country\Controller\PublicCountryController;
use App\Modules\CountryKpa\Controller\PublicCountryKpaController;
use App\Modules\Indicator\Controller\IndicatorController;
use App\Modules\IndicatorType\Controller\IndicatorTypeController;
use App\Modules\Kpa\Controller\PublicKpaController;
use App\Modules\Measure\Controller\MeasureController;
use App\Modules\Measure\Controller\PublicMeasureController;
use App\Modules\Project\Controller\PublicProjectController;
use App\Modules\Program\Controller\PublicProgramController;
use App\Modules\ProgramState\Controller\PublicProgramStateController;
use App\Modules\ProjectState\Controller\PublicProjectStateController;
use App\Modules\Statistics\Controller\StatisticsController;
use App\Modules\StrategicOutput\Controller\PublicStrategicOutputController;
use Illuminate\Support\Facades\Route;
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
use App\Modules\ProgramCountryUserRole\Controller\ProgramCountryUserRoleController;
use App\Modules\InviteProgram\Controller\InviteProgramController;
use App\Modules\UserRole\Controller\UserRoleController;
use \App\Modules\StrategicOutput\Controller\StrategicOutputController;
use App\Modules\Project\Controller\ProjectController;
use App\Modules\ProjectAgency\Controller\ProjectAgencyController;
use App\Modules\ProjectIndicator\Controller\ProjectIndicatorController;
use App\Modules\Permission\Controller\PermissionController;
use App\Modules\Role\Controller\RoleController;
use App\Modules\UserState\Controller\UserStateController;
use App\Modules\User\Controller\UserController;
use App\Modules\Stats\Controller\StatsController;

Route::prefix('v1')->group(function () {
    // Public auth routes
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);

    // Email verification routes (no authentication required)
    Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->name('verification.verify');
    Route::post('email/resend', [VerificationController::class, 'resend'])
        ->middleware('throttle:3,1')  // 3 requests per minute
        ->name('verification.resend');

    // Protected routes - require JWT token
    Route::middleware('jwt')->group(function () {
        // Auth endpoints
        Route::post('auth/refresh', [AuthController::class, 'refresh']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::get('auth/permissions', [AuthController::class, 'permissions']);

        // API Routes para Donors
        Route::get('donors', [DonorController::class, 'index'])->middleware('scope:donors');
        Route::post('donors', [DonorController::class, 'store'])->middleware('scope:donors:write');
        Route::get('donors/search', [DonorController::class, 'search'])->middleware('scope:donors');
        Route::get('donors/{id}', [DonorController::class, 'show'])->middleware('scope:donors');
        Route::put('donors/{id}', [DonorController::class, 'update'])->middleware('scope:donors:write');
        Route::get('donors/get/project', [DonorController::class, 'getDonorsExcluding'])->middleware('scope:donors');

        // API Routes para Beneficiaries
        Route::get('beneficiaries', [BeneficiaryController::class, 'index'])->middleware('scope:beneficiaries');
        Route::post('beneficiaries', [BeneficiaryController::class, 'store'])->middleware('scope:beneficiaries:write');
        Route::get('beneficiaries/search', [BeneficiaryController::class, 'search'])->middleware('scope:beneficiaries');
        Route::get('beneficiaries/{id}', [BeneficiaryController::class, 'show'])->middleware('scope:beneficiaries');
        Route::put('beneficiaries/{id}', [BeneficiaryController::class, 'update'])->middleware('scope:beneficiaries:write');

        // API Routes para ProgramStates
        Route::get('program_states', [ProgramStateController::class, 'index'])->middleware('scope:program_states');
        Route::post('program_states', [ProgramStateController::class, 'store'])->middleware('scope:program_states:write');
        Route::get('program_states/search', [ProgramStateController::class, 'search'])->middleware('scope:program_states');
        Route::get('program_states/{id}', [ProgramStateController::class, 'show'])->middleware('scope:program_states');
        Route::put('program_states/{id}', [ProgramStateController::class, 'update'])->middleware('scope:program_states:write');

        // API Routes para Countries
        Route::get('countries', [CountryController::class, 'index'])->middleware('scope:countries');
        Route::post('countries', [CountryController::class, 'store'])->middleware('scope:countries:write');
        Route::get('countries/search', [CountryController::class, 'search'])->middleware('scope:countries');
        Route::get('countries/{id}', [CountryController::class, 'show'])->middleware('scope:countries');
        Route::put('countries/{id}', [CountryController::class, 'update'])->middleware('scope:countries:write');

        // API Routes para Currencies
        Route::get('currencies', [CurrencyController::class, 'index'])->middleware('scope:currencies');
        Route::post('currencies', [CurrencyController::class, 'store'])->middleware('scope:currencies:write');
        Route::get('currencies/search', [CurrencyController::class, 'search'])->middleware('scope:currencies');
        Route::get('currencies/{id}', [CurrencyController::class, 'show'])->middleware('scope:currencies');
        Route::put('currencies/{id}', [CurrencyController::class, 'update'])->middleware('scope:currencies:write');

        // API Routes para Kpas
        Route::get('kpas', [KpaController::class, 'index'])->middleware('scope:kpas');
        Route::post('kpas', [KpaController::class, 'store'])->middleware('scope:kpas:write');
        Route::get('kpas/search', [KpaController::class, 'search'])->middleware('scope:kpas');
        Route::get('kpas/{id}', [KpaController::class, 'show'])->middleware('scope:kpas');
        Route::put('kpas/{id}', [KpaController::class, 'update'])->middleware('scope:kpas:write');

        // API Routes para Users
        Route::get('users/pending', [UserController::class, 'pending'])->middleware('scope:users:write');
        Route::get('users/unverified', [UserController::class, 'unverified'])->middleware('scope:users:write');
        Route::post('users/{id}/approve', [UserController::class, 'approve'])->middleware('scope:users:write');
        Route::delete('users/{id}/reject', [UserController::class, 'reject'])->middleware('scope:users:write');
        Route::put('users/{id}/state', [UserController::class, 'changeUserState'])->middleware('scope:users:write');
        Route::get('users', [UserController::class, 'index'])->middleware('scope:users');
        Route::post('users', [UserController::class, 'store'])->middleware('scope:users:write');
        Route::get('users/search', [UserController::class, 'search'])->middleware('scope:users');
        Route::get('users/{id}', [UserController::class, 'show'])->middleware('scope:users');
        Route::put('users/{id}', [UserController::class, 'update'])->middleware('scope:users:write');

        // API Routes para Permissions (listar permisos disponibles)
        Route::get('permissions', [PermissionController::class, 'index'])->middleware('scope:roles');

        // API Routes para Roles
        Route::get('roles', [RoleController::class, 'index'])->middleware('scope:roles');
        Route::post('roles', [RoleController::class, 'store'])->middleware('scope:roles:write');
        Route::get('roles/search', [RoleController::class, 'search'])->middleware('scope:roles');
        Route::get('roles/{id}', [RoleController::class, 'show'])->middleware('scope:roles');
        Route::put('roles/{id}', [RoleController::class, 'update'])->middleware('scope:roles:write');

        // API Routes para Role Permissions (gestión de permisos de roles)
        Route::get('roles/{roleId}/permissions', [RoleController::class, 'getPermissions'])->middleware('scope:roles');
        Route::post('roles/{roleId}/permissions', [RoleController::class, 'assignPermission'])->middleware('scope:roles:write');
        Route::delete('roles/{roleId}/permissions/{permissionId}', [RoleController::class, 'removePermission'])->middleware('scope:roles:write');

        // API Routes para UserState 
        Route::get('user_states', [UserStateController::class, 'index'])->middleware('scope:user_states');
        Route::post('user_states', [UserStateController::class, 'store'])->middleware('scope:user_states:write');
        Route::get('user_states/search', [UserStateController::class, 'search'])->middleware('scope:user_states');
        Route::get('user_states/{id}', [UserStateController::class, 'show'])->middleware('scope:user_states');
        Route::put('user_states/{id}', [UserStateController::class, 'update'])->middleware('scope:user_states:write');

        // API Routes para CountryKpa-Users (User assignments to CountryKpas)
        Route::get('country_kpa_users', [CountryKpaUserController::class, 'index'])->middleware('scope:country_kpa_users');
        Route::post('country_kpa_users', [CountryKpaUserController::class, 'store'])->middleware('scope:country_kpa_users:write');
        Route::get('country_kpa_users/{id}', [CountryKpaUserController::class, 'show'])->middleware('scope:country_kpa_users');
        Route::put('country_kpa_users/{id}', [CountryKpaUserController::class, 'update'])->middleware('scope:country_kpa_users:write');
        Route::delete('country_kpa_users/{id}', [CountryKpaUserController::class, 'destroy'])->middleware('scope:country_kpa_users:write');

        // API Routes para User-Roles (Role assignments to Users)
        Route::get('user_roles', [UserRoleController::class, 'index'])->middleware('scope:user_roles');
        Route::post('user_roles', [UserRoleController::class, 'store'])->middleware('scope:user_roles:write');
        Route::get('user_roles/{id}', [UserRoleController::class, 'show'])->middleware('scope:user_roles');
        Route::put('user_roles/{id}', [UserRoleController::class, 'update'])->middleware('scope:user_roles:write');
        Route::delete('user_roles/{id}', [UserRoleController::class, 'destroy'])->middleware('scope:user_roles:write');

        // API Routes para Program-Users (CountryKpaUser assignments to Programs)
        Route::get('program_users', [ProgramUserController::class, 'index'])->middleware('scope:program_users');
        Route::post('program_users', [ProgramUserController::class, 'store'])->middleware('scope:program_users:write');
        Route::get('program_users/{id}', [ProgramUserController::class, 'show'])->middleware('scope:program_users');
        Route::put('program_users/{id}', [ProgramUserController::class, 'update'])->middleware('scope:program_users:write');
        Route::delete('program_users/{id}', [ProgramUserController::class, 'destroy'])->middleware('scope:program_users:write');

        // API Routes para Country-Kpas
        Route::get('country_kpas', [CountryKpaController::class, 'index'])->middleware('scope:country_kpas');
        Route::post('country_kpas', [CountryKpaController::class, 'store'])->middleware('scope:country_kpas:write');
        Route::get('country_kpas/{id}', [CountryKpaController::class, 'show'])->middleware('scope:country_kpas');
        Route::get('country_kpas/country/{id}', [CountryKpaController::class, 'showForCountry'])->middleware('scope:country_kpas');
        Route::put('country_kpas/{id}', [CountryKpaController::class, 'update'])->middleware('scope:country_kpas:write');
        Route::delete('country_kpas/{id}', [CountryKpaController::class, 'destroy'])->middleware('scope:country_kpas:write');

        // API Routes para Contacts
        Route::get('contacts', [ContactController::class, 'index'])->middleware('scope:contacts');
        Route::post('contacts', [ContactController::class, 'store'])->middleware('scope:contacts:write');
        Route::get('contacts/search', [ContactController::class, 'search'])->middleware('scope:contacts');
        Route::get('contacts/{id}', [ContactController::class, 'show'])->middleware('scope:contacts');
        Route::put('contacts/{id}', [ContactController::class, 'update'])->middleware('scope:contacts:write');

        // API Routes para ProjectStates
        Route::get('project-states', [ProjectStateController::class, 'index'])->middleware('scope:project_states');
        Route::post('project-states', [ProjectStateController::class, 'store'])->middleware('scope:project_states:write');
        Route::get('project-states/{id}', [ProjectStateController::class, 'show'])->middleware('scope:project_states');
        Route::put('project-states/{id}', [ProjectStateController::class, 'update'])->middleware('scope:project_states:write');

        // API Routes para SDG
        Route::get('sdgs', [SdgController::class, 'index'])->middleware('scope:sdgs');
        Route::post('sdgs', [SdgController::class, 'store'])->middleware('scope:sdgs:write');
        Route::get('sdgs/search', [SdgController::class, 'search'])->middleware('scope:sdgs');
        Route::get('sdgs/{id}', [SdgController::class, 'show'])->middleware('scope:sdgs');
        Route::put('sdgs/{id}', [SdgController::class, 'update'])->middleware('scope:sdgs:write');

        // API Routes para Agency
        Route::get('agencies', [AgencyController::class, 'index'])->middleware('scope:agencies');
        Route::post('agencies', [AgencyController::class, 'store'])->middleware('scope:agencies:write');
        Route::get('agencies/search', [AgencyController::class, 'search'])->middleware('scope:agencies');
        Route::get('agencies/{id}', [AgencyController::class, 'show'])->middleware('scope:agencies');
        Route::put('agencies/{id}', [AgencyController::class, 'update'])->middleware('scope:agencies:write');
        Route::get('agencies/get/project', [AgencyController::class, 'getAgenciesExcluding'])->middleware('scope:agencies');

        // API Routes para Indicator Type
        Route::get('indicator-types', [IndicatorTypeController::class, 'index'])->middleware('scope:indicator_types');
        Route::post('indicator-types', [IndicatorTypeController::class, 'store'])->middleware('scope:indicator_types:write');
        Route::get('indicator-types/search', [IndicatorTypeController::class, 'search'])->middleware('scope:indicator_types');
        Route::get('indicator-types/{id}', [IndicatorTypeController::class, 'show'])->middleware('scope:indicator_types');
        Route::put('indicator-types/{id}', [IndicatorTypeController::class, 'update'])->middleware('scope:indicator_types:write');

        // API Routes para Indicator
        Route::get('indicators', [IndicatorController::class, 'index'])->middleware('scope:indicators');
        Route::post('indicators', [IndicatorController::class, 'store'])->middleware('scope:indicators:write');
        Route::get('indicators/search', [IndicatorController::class, 'search'])->middleware('scope:indicators');
        Route::get('indicators/{id}', [IndicatorController::class, 'show'])->middleware('scope:indicators');
        Route::put('indicators/{id}', [IndicatorController::class, 'update'])->middleware('scope:indicators:write');
        Route::get('indicators/measure/{id}', [IndicatorController::class, 'getIndicatorsByMeasureId'])->middleware('scope:indicators');

        // API Routes para Measure
        Route::get('measures', [MeasureController::class, 'index'])->middleware('scope:measures');
        Route::post('measures', [MeasureController::class, 'store'])->middleware('scope:measures:write');
        Route::put('measures/{id}', [MeasureController::class, 'update'])->middleware('scope:measures:write');
        Route::get('measures/search', [MeasureController::class, 'search'])->middleware('scope:measures');
        Route::get('measures/{id}', [MeasureController::class, 'show'])->middleware('scope:measures');
        Route::get('measures/strategic-output/{id}', [MeasureController::class, 'listByStrategicOutput'])->middleware('scope:measures');
        Route::post('measures-indicators', [MeasureController::class, 'addIndicator'])->middleware('scope:measures:write');
        Route::get('measures-indicators/{id}', [MeasureController::class, 'showWithIndicators'])->middleware('scope:measures');
        Route::get('measures/{id}/indicators/{name}', [MeasureController::class, 'getIndicatorByName'])->middleware('scope:measures');
        Route::post('measures/remove-indicator', [MeasureController::class, 'removeIndicator'])->middleware('scope:measures:write');
        Route::get('measures/get/strategic-output/{id}', [MeasureController::class, 'measuresListByStrategicOutput'])->middleware('scope:measures');

        // API Routes para Strategic Outputs
        Route::get('strategic-outputs', [StrategicOutputController::class, 'index'])->middleware('scope:strategic_outputs');
        Route::post('strategic-outputs', [StrategicOutputController::class, 'store'])->middleware('scope:strategic_outputs:write');
        Route::get('strategic-outputs/search', [StrategicOutputController::class, 'search'])->middleware('scope:strategic_outputs');
        Route::get('strategic-outputs/{id}', [StrategicOutputController::class, 'show'])->middleware('scope:strategic_outputs');
        Route::put('strategic-outputs/{id}', [StrategicOutputController::class, 'update'])->middleware('scope:strategic_outputs:write');
        Route::get('strategic-outputs/country-kpa/{id}', [StrategicOutputController::class, 'showByCountryKpa'])->middleware('scope:strategic_outputs');
        Route::post('strategic-outputs-measures', [StrategicOutputController::class, 'addMeasure'])->middleware('scope:strategic_outputs:write');
        Route::post('strategic-outputs/remove-measure', [StrategicOutputController::class, 'removeMeasure'])->middleware('scope:strategic_outputs:write');
        Route::get('strategic-outputs/kpa/{id}', [StrategicOutputController::class, 'getStrategicOutputsForKpaId'])->middleware('scope:strategic_outputs');

        // API Routes para Programs
        Route::get('programs', [ProgramController::class, 'index'])->middleware('scope:programs');
        Route::post('programs', [ProgramController::class, 'store'])->middleware('scope:programs:write');
        Route::get('programs/search', [ProgramController::class, 'search'])->middleware('scope:programs');
        Route::get('programs/{id}', [ProgramController::class, 'show'])->middleware('scope:programs');
        Route::put('programs/{id}', [ProgramController::class, 'update'])->middleware('scope:programs:write');

        // API Routes para Program-CountryUserRole assignments
        Route::get('program_country_user_roles', [ProgramCountryUserRoleController::class, 'index'])->middleware('scope:program_country_user_roles');
        Route::post('program_country_user_roles', [ProgramCountryUserRoleController::class, 'store'])->middleware('scope:program_country_user_roles:write');
        Route::get('program_country_user_roles/{id}', [ProgramCountryUserRoleController::class, 'show'])->middleware('scope:program_country_user_roles');
        Route::delete('program_country_user_roles/{id}', [ProgramCountryUserRoleController::class, 'destroy'])->middleware('scope:program_country_user_roles:write');

        // API Routes para InviteProgram (project-manager invitations by relation existence)
        Route::get('invite_programs/candidates', [InviteProgramController::class, 'candidates'])->middleware('scope:program_country_user_roles');
        Route::get('invite_programs', [InviteProgramController::class, 'index'])->middleware('scope:program_country_user_roles');
        Route::post('invite_programs', [InviteProgramController::class, 'store'])->middleware('scope:program_country_user_roles:write');
        Route::get('invite_programs/{id}', [InviteProgramController::class, 'show'])->middleware('scope:program_country_user_roles');
        Route::delete('invite_programs/{id}', [InviteProgramController::class, 'destroy'])->middleware('scope:program_country_user_roles:write');

        // API Routes para Project
        Route::get('projects', [ProjectController::class, 'index'])->middleware('scope:projects');
        Route::post('projects', [ProjectController::class, 'store'])->middleware('scope:projects:write');
        Route::get('projects/search', [ProjectController::class, 'search'])->middleware('scope:projects');
        Route::get('projects/{id}', [ProjectController::class, 'show'])->middleware('scope:projects');
        Route::put('projects/{id}', [ProjectController::class, 'update'])->middleware('scope:projects:write');
        Route::get('projects/program/{id}', [ProjectController::class, 'getProjectsByProgramId'])->middleware('scope:projects');
        Route::get('projects/program/{programId}/kpas', [ProjectController::class, 'getProgramKpas'])->middleware('scope:projects');
        Route::get('projects/program/{programId}/kpas/{kpaId}/strategic-outputs', [ProjectController::class, 'getProgramStrategicOutputsByKpa'])->middleware('scope:projects');
        Route::get('projects/program/{programId}/strategic-outputs/{strategicOutputId}/measures', [ProjectController::class, 'getProgramMeasuresByStrategicOutput'])->middleware('scope:projects');
        Route::get('projects/program/{programId}/measures/{measureId}/indicators', [ProjectController::class, 'getProgramIndicatorsByMeasure'])->middleware('scope:projects');

        // Listar todas las relaciones proyecto-agencia
        Route::get('project-agencies', [ProjectAgencyController::class, 'index'])->middleware('scope:project_agencies');
        Route::post('project-agencies', [ProjectAgencyController::class, 'createProjectAgency'])->middleware('scope:project_agencies:write');
        Route::delete('project-agencies', [ProjectAgencyController::class, 'deleteProjectAgency'])->middleware('scope:project_agencies:write');
        Route::get('project-agencies/projects/by-agency/{id}', [ProjectAgencyController::class, 'showProjectsByAgencyId'])->middleware('scope:project_agencies');
        Route::get('project-agencies/projects/by-agency-name/{name}', [ProjectAgencyController::class, 'showProjectsByAgencyName'])->middleware('scope:project_agencies');
        Route::get('project-agencies/agencies/by-project/{id}', [ProjectAgencyController::class, 'showAgenciesByProjectId'])->middleware('scope:project_agencies');
        Route::get('project-agencies/agencies/by-project-name/{name}', [ProjectAgencyController::class, 'showAgenciesByProjectName'])->middleware('scope:project_agencies');

        // Listar todas las relaciones proyecto-indicador
        Route::get('project-indicators', [ProjectIndicatorController::class, 'index'])->middleware('scope:projects');
        Route::post('project-indicators', [ProjectIndicatorController::class, 'createProjectIndicator'])->middleware('scope:projects:write');
        Route::delete('project-indicators', [ProjectIndicatorController::class, 'deleteProjectIndicator'])->middleware('scope:projects:write');
        Route::get('project-indicators/projects/by-indicator/{id}', [ProjectIndicatorController::class, 'showProjectsByIndicatorId'])->middleware('scope:projects');
        Route::get('project-indicators/projects/by-indicator/{id}', [ProjectIndicatorController::class, 'showProjectsByIndicatorId'])->middleware('scope:projects');
        Route::get('project-indicators/indicators/by-project/{id}', [ProjectIndicatorController::class, 'showIndicatorsByProjectId'])->middleware('scope:projects');
        Route::get('project-indicators/indicators/by-project-name/{name}', [ProjectIndicatorController::class, 'showIndicatorsByProjectName'])->middleware('scope:projects');

        Route::get('stats/dashboard', [StatsController::class, 'getDashboardStats'])->middleware('scope:stats');
        Route::get('stats/programs-by-state', [StatsController::class, 'getProgramsByState'])->middleware('scope:stats');
        Route::get('stats/projects-by-state', [StatsController::class, 'getProjectsByState'])->middleware('scope:stats');
        Route::get('stats/projects-per-program', [StatsController::class, 'getProjectsPerProgram'])->middleware('scope:stats');
        Route::get('stats/projects-timeline', [StatsController::class, 'getProjectsTimeline'])->middleware('scope:stats');
        Route::get('stats/projects-progress', [StatsController::class, 'getProjectsProgress'])->middleware('scope:stats');
    });
});

Route::prefix('v1/public')->group(function () {

    // Programs
    Route::post('programs', [PublicProgramController::class, 'index']);
    Route::get('programs/{id}', [PublicProgramController::class, 'show']);

    // Projects    
    Route::post('projects', [PublicProjectController::class, 'index']);
    Route::get('projects/{id}', [PublicProjectController::class, 'show']);

    // Countries
    Route::get('countries', [PublicCountryController::class, 'index']);

    // CountryKPAs
    Route::get('kpas/{id}', [PublicCountryKpaController::class, 'getAllByCountryId']);

    // KPAs
    Route::get('kpas', [PublicKpaController::class, 'index']);

    //Strategic Outputs
    Route::get('strategic-outputs/{id}', [PublicStrategicOutputController::class, 'getStrategicOutputsByKpaId']);

    // Measures
    Route::get('measures/{id}', [PublicMeasureController::class, 'getMMeasuresByStrategicOutputId']);

    // Project-states
    Route::get('project-states', [PublicProjectStateController::class, 'index']);

    // Program-states
    Route::get('program-states', [PublicProgramStateController::class, 'index']);

    // Statistics
    Route::get('measure-implementation/{id}', [StatisticsController::class, 'getMeasureImplementation']);
    Route::get('strategic-output-implementation/{id}', [StatisticsController::class, 'getStrategicOutputImplementation']);
    Route::get('kpa-implementation/{id}', [StatisticsController::class, 'getKpaImplementation']);
    Route::get('overall-implementation', [StatisticsController::class, 'getOverallImplementation']);
    Route::get('allkpas-implementation', [StatisticsController::class, 'getAllKpasImplementation']);
});
