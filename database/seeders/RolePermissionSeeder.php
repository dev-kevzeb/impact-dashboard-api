<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = Role::where('name', 'admin')->where('guard_name', 'api')->first();
        if ($admin) {
            $wildcardPermission = Permission::where('name', '*:*')->first();
            if ($wildcardPermission) {
                $admin->syncPermissions([$wildcardPermission]);
            }
        }

        $projectManager = Role::where('name', 'project-manager')->where('guard_name', 'api')->first();
        if ($projectManager) {
            $projectManagerPermissions = [
                'programs:read',
                'programs:write',
                'program_states:read',
                'program_users:read',
                'program_users:write',
                'program_country_user_roles:read',
                'program_country_user_roles:write',
                'projects:read',
                'projects:write',
                'project_states:read',
                'project_agencies:read',
                'project_agencies:write',
                'project_indicators:read',
                'project_indicators:write',
                'beneficiaries:read',
                'beneficiaries:write',
                'donors:read',
                'agencies:read',
                'sdgs:read',
                'indicators:read',
                'indicator_types:read',
            ];

            $permissions = Permission::whereIn('name', $projectManagerPermissions)->get();
            $projectManager->syncPermissions($permissions);
        }

        $countryManager = Role::where('name', 'country-manager')->where('guard_name', 'api')->first();
        if ($countryManager) {
            $countryManagerPermissions = [
                'kpas:read',
                'kpas:write',
                'countries:read',
                'countries:write',
                'country_kpas:read',
                'country_kpas:write',
                'country_kpa_users:read',
                'country_kpa_users:write',
                'strategic_outputs:read',
                'strategic_outputs:write',
                'measures:read',
                'measures:write',
                'indicators:read',
                'indicator_types:read',
                'programs:read',
                'program_states:read',
                'projects:read',
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
