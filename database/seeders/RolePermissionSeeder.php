<?php

namespace Database\Seeders;

use App\Modules\Donor\Domain\Donor;
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
                'admin_dashboard:read',
                'programs:read',
                'projects:read',
                'projects:weight',
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
                'countries:activate',
                'currencies:read',
                'currencies:write',
                'donors:read',
                'donors:create',
                'donors:update',
                'donors:delete',
                'beneficiaries:read',
                'beneficiaries:write',
                'projects:progress',
                'kpas:read',
                'kpas:write',
                'indicator_types:read',
                'indicator_types:write',
                'agencies:read',
                'agencies:write',
                'country_dashboard_shares:read',
                'country_join_requests:read',
                'country_kpas:write',
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
                'projects:progress',
                'projects:write',
                'projects:create',
                'projects:delete',
                'projects:weight',
                'contacts:read',
                'contacts:write',
                'programs:view_by_country',
                'projects:view_by_country',
                'country_join_requests:read',
                'country_join_requests:write',
                'donors:read',
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
                'projects:view_by_country',
                'projects:weight',
                'projects:progress',
                'donors:read',
                'donors:create',
                'country_dashboard_shares:read',
                'country_dashboard_shares:write',
                'country_join_requests:read',
                'country_join_requests:write',
                'countries:activate',
            ];

            $permissions = Permission::whereIn('name', $countryManagerPermissions)->get();
            $countryManager->syncPermissions($permissions);
        }
    }
}
