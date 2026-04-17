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
                'programs:read',
                'projects:read',
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
                'program_country_user_roles:read',
                'program_country_user_roles:write',
                'projects:read',
                'projects:write',
                'projects:create',
                'projects:delete',
                'contacts:read',
                'contacts:write',
            ];

            $permissions = Permission::whereIn('name', $projectManagerPermissions)->get();
            $projectManager->syncPermissions($permissions);
        }

        $countryManager = Role::where('name', 'country-manager')->where('guard_name', 'api')->first();
        if ($countryManager) {
            $countryManagerPermissions = [
                'programs:read',
                'programs:view_by_country',
                'projects:read',
                'projects:weight',
                'donors:read',
                'donors:write',
            ];

            $permissions = Permission::whereIn('name', $countryManagerPermissions)->get();
            $countryManager->syncPermissions($permissions);
        }
    }
}
