<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = Role::where('name', 'admin')->where('guard_name', 'api')->first();
        if ($admin) {
            $adminPermissions = [
                'users:read',
                'users:write',
                'user_states:read',
                'user_states:write',
                'program_states:read',
                'program_states:write',
                'project_states:read',
                'project_states:write',
                'measures:read',
                'measures:write',
                'sdgs:read',
                'sdgs:write',
                'countries:read',
                'countries:write',
                'currencies:read',
                'currencies:write',
                'donors:read',
                'donors:write',
                'beneficiaries:read',
                'beneficiaries:write',
                'kpas:read',
                'kpas:write',
                'indicator_types:read',
                'indicator_types:write',
                'agencies:read',
                'agencies:write',
            ];

            $permissions = Permission::whereIn('name', $adminPermissions)->get();
            $admin->syncPermissions($permissions);
        }

        $projectManager = Role::where('name', 'project-manager')->where('guard_name', 'api')->first();
        if ($projectManager) {
            $projectManagerPermissions = [
                'programs:read',
                'programs:write',
                'program_states:read',
                'program_country_user_roles:read',
                'program_country_user_roles:write',
                'projects:read',
                'projects:write',
                'projects:create',
                'projects:delete',
                'kpas:read',
                'kpas:write',
                'project_states:read',
                'project_states:write',
                'project_agencies:read',
                'project_agencies:write',
                'project_indicators:read',
                'project_indicators:write',
                'beneficiaries:read',
                'beneficiaries:write',
                'donors:read',
                'donors:write',
                'agencies:read',
                'sdgs:read',
                'strategic_outputs:read',
                'measures:read',
                'indicators:read',
                'indicators:write',
                'indicator_types:read',
                'indicator_types:write',
                'countries:read',
                'countries:write',
                'country_kpas:read',
                'country_kpas:write',
            ];

            $permissions = Permission::whereIn('name', $projectManagerPermissions)->get();
            $projectManager->syncPermissions($permissions);
        }

        $countryManager = Role::where('name', 'country-manager')->where('guard_name', 'api')->first();
        if ($countryManager) {
            $countryManagerPermissions = [
                'kpas:read',
                'kpas:write',
                'currencies:read',
                'countries:read',
                'countries:write',
                'country_kpas:read',
                'country_kpas:write',
                'strategic_outputs:read',
                'strategic_outputs:write',
                'measures:read',
                'measures:write',
                'indicators:read',
                'indicators:write',
                'indicator_types:read',
                'programs:read',
                'programs:view_by_country',
                'program_states:read',
                'program_country_user_roles:read',
                'projects:read',
                'projects:write',
                'projects:weight',
                'project_states:read',
                'users:read',
                'users:write',
                'user_roles:read',
                'user_roles:write',
                'user_states:read',
            ];

            $permissions = Permission::whereIn('name', $countryManagerPermissions)->get();
            $countryManager->syncPermissions($permissions);
        }
    }
}
