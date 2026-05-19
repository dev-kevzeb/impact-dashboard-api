<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            ['name' => '*:*', 'scope' => 'all', 'module' => 'admin', 'description' => 'Full system access - Admin only'],

            ['name' => 'admin_dashboard:read', 'scope' => 'admin_dashboard', 'module' => 'AdminDashboard', 'description' => 'View admin dashboard (shared countries)'],

            ['name' => 'donors:read', 'scope' => 'donors', 'module' => 'Donor', 'description' => 'View donors'],
            ['name' => 'donors:create', 'scope' => 'donors', 'module' => 'Donor', 'description' => 'Create donors'],
            ['name' => 'donors:update', 'scope' => 'donors', 'module' => 'Donor', 'description' => 'Edit donors'],
            ['name' => 'donors:delete', 'scope' => 'donors', 'module' => 'Donor', 'description' => 'Delete donors'],

            ['name' => 'beneficiaries:read', 'scope' => 'beneficiaries', 'module' => 'Beneficiary', 'description' => 'View beneficiaries'],
            ['name' => 'beneficiaries:write', 'scope' => 'beneficiaries', 'module' => 'Beneficiary', 'description' => 'Create/edit beneficiaries'],

            ['name' => 'programs:read', 'scope' => 'programs', 'module' => 'Program', 'description' => 'View programs'],
            ['name' => 'programs:write', 'scope' => 'programs', 'module' => 'Program', 'description' => 'Create/edit programs'],
            ['name' => 'programs:view_by_country', 'scope' => 'programs', 'module' => 'Program', 'description' => 'View programs filtered by assigned country'],

            ['name' => 'projects:read', 'scope' => 'projects', 'module' => 'Project', 'description' => 'View projects'],
            ['name' => 'projects:view_by_country', 'scope' => 'projects', 'module' => 'Project', 'description' => 'View projects filtered by assigned country'],
            ['name' => 'projects:progress', 'scope' => 'projects', 'module' => 'Project', 'description' => 'Edit project progress from dashboard'],
            ['name' => 'projects:write', 'scope' => 'projects', 'module' => 'Project', 'description' => 'Create/edit projects'],
            ['name' => 'projects:create', 'scope' => 'projects', 'module' => 'Project', 'description' => 'Create new projects'],
            ['name' => 'projects:delete', 'scope' => 'projects', 'module' => 'Project', 'description' => 'Delete projects'],
            ['name' => 'projects:weight', 'scope' => 'projects', 'module' => 'Project', 'description' => 'Edit project weight'],

            ['name' => 'currencies:read', 'scope' => 'currencies', 'module' => 'Currency', 'description' => 'View currencies'],
            ['name' => 'currencies:write', 'scope' => 'currencies', 'module' => 'Currency', 'description' => 'Create/edit currencies'],

            ['name' => 'contacts:read', 'scope' => 'contacts', 'module' => 'Contact', 'description' => 'View contacts'],
            ['name' => 'contacts:write', 'scope' => 'contacts', 'module' => 'Contact', 'description' => 'Create/edit contacts'],

            ['name' => 'countries:read', 'scope' => 'countries', 'module' => 'Country', 'description' => 'View countries'],
            ['name' => 'countries:write', 'scope' => 'countries', 'module' => 'Country', 'description' => 'Create/edit countries'],
            ['name' => 'countries:activate', 'scope' => 'countries', 'module' => 'Country', 'description' => 'Activate/deactivate countries'],

            ['name' => 'agencies:read', 'scope' => 'agencies', 'module' => 'Agency', 'description' => 'View agencies'],
            ['name' => 'agencies:write', 'scope' => 'agencies', 'module' => 'Agency', 'description' => 'Create/edit agencies'],

            ['name' => 'sdgs:read', 'scope' => 'sdgs', 'module' => 'Sdg', 'description' => 'View SDGs (Sustainable Development Goals)'],
            ['name' => 'sdgs:write', 'scope' => 'sdgs', 'module' => 'Sdg', 'description' => 'Create/edit SDGs'],

            ['name' => 'kpas:read', 'scope' => 'kpas', 'module' => 'Kpa', 'description' => 'View KPAs'],
            ['name' => 'kpas:write', 'scope' => 'kpas', 'module' => 'Kpa', 'description' => 'Create/edit KPAs'],

            ['name' => 'country_kpas:read', 'scope' => 'country_kpas', 'module' => 'CountryKpa', 'description' => 'View country-KPA relationships'],
            ['name' => 'country_kpas:write', 'scope' => 'country_kpas', 'module' => 'CountryKpa', 'description' => 'Create/edit country-KPA relationships'],

            ['name' => 'country_dashboard_shares:read', 'scope' => 'country_dashboard_shares', 'module' => 'CountryDashboardShare', 'description' => 'View country dashboard share relationships'],
            ['name' => 'country_dashboard_shares:write', 'scope' => 'country_dashboard_shares', 'module' => 'CountryDashboardShare', 'description' => 'Create/delete country dashboard share relationships'],

            ['name' => 'country_join_requests:read', 'scope' => 'country_join_requests', 'module' => 'CountryJoinRequest', 'description' => 'View country join requests'],
            ['name' => 'country_join_requests:write', 'scope' => 'country_join_requests', 'module' => 'CountryJoinRequest', 'description' => 'Create/review country join requests'],

            ['name' => 'strategic_outputs:read', 'scope' => 'strategic_outputs', 'module' => 'StrategicOutput', 'description' => 'View strategic outputs'],
            ['name' => 'strategic_outputs:write', 'scope' => 'strategic_outputs', 'module' => 'StrategicOutput', 'description' => 'Create/edit strategic outputs'],

            ['name' => 'measures:read', 'scope' => 'measures', 'module' => 'Measure', 'description' => 'View measures'],
            ['name' => 'measures:write', 'scope' => 'measures', 'module' => 'Measure', 'description' => 'Create/edit measures'],

            ['name' => 'indicators:read', 'scope' => 'indicators', 'module' => 'Indicator', 'description' => 'View indicators'],
            ['name' => 'indicators:write', 'scope' => 'indicators', 'module' => 'Indicator', 'description' => 'Create/edit indicators'],

            ['name' => 'indicator_types:read', 'scope' => 'indicator_types', 'module' => 'IndicatorType', 'description' => 'View indicator types'],
            ['name' => 'indicator_types:write', 'scope' => 'indicator_types', 'module' => 'IndicatorType', 'description' => 'Create/edit indicator types'],

            ['name' => 'program_states:read', 'scope' => 'program_states', 'module' => 'ProgramState', 'description' => 'View program states'],
            ['name' => 'program_states:write', 'scope' => 'program_states', 'module' => 'ProgramState', 'description' => 'Create/edit program states'],

            ['name' => 'program_country_user_roles:read', 'scope' => 'program_country_user_roles', 'module' => 'ProgramCountryUserRole', 'description' => 'View program-country-user-role assignments'],
            ['name' => 'program_country_user_roles:write', 'scope' => 'program_country_user_roles', 'module' => 'ProgramCountryUserRole', 'description' => 'Assign programs to country user roles'],

            ['name' => 'project_states:read', 'scope' => 'project_states', 'module' => 'ProjectState', 'description' => 'View project states'],
            ['name' => 'project_states:write', 'scope' => 'project_states', 'module' => 'ProjectState', 'description' => 'Create/edit project states'],

            ['name' => 'project_agencies:read', 'scope' => 'project_agencies', 'module' => 'ProjectAgency', 'description' => 'View project-agency relationships'],
            ['name' => 'project_agencies:write', 'scope' => 'project_agencies', 'module' => 'ProjectAgency', 'description' => 'Assign agencies to projects'],

            ['name' => 'project_indicators:read', 'scope' => 'project_indicators', 'module' => 'ProjectIndicator', 'description' => 'View project-indicator relationships'],
            ['name' => 'project_indicators:write', 'scope' => 'project_indicators', 'module' => 'ProjectIndicator', 'description' => 'Assign indicators to projects'],

            ['name' => 'users:read', 'scope' => 'users', 'module' => 'User', 'description' => 'View users'],
            ['name' => 'users:write', 'scope' => 'users', 'module' => 'User', 'description' => 'Create/edit users, approve registrations'],

            ['name' => 'roles:read', 'scope' => 'roles', 'module' => 'Role', 'description' => 'View roles'],
            ['name' => 'roles:write', 'scope' => 'roles', 'module' => 'Role', 'description' => 'Create/edit roles'],

            ['name' => 'user_states:read', 'scope' => 'user_states', 'module' => 'UserState', 'description' => 'View user states'],
            ['name' => 'user_states:write', 'scope' => 'user_states', 'module' => 'UserState', 'description' => 'Create/edit user states'],

            ['name' => 'user_roles:read', 'scope' => 'user_roles', 'module' => 'UserRole', 'description' => 'View user role assignments'],
            ['name' => 'user_roles:write', 'scope' => 'user_roles', 'module' => 'UserRole', 'description' => 'Assign roles to users'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                [
                    'name' => $permission['name'],
                    'guard_name' => 'api',
                ],
                [
                    'scope' => $permission['scope'],
                    'module' => $permission['module'],
                    'description' => $permission['description'],
                ]
            );
        }
    }
}
