<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Roles existentes en sistema:
     * - admin: Full access (wildcard *:*)
     * - project-manager: Gestiona proyectos, programas, beneficiarios, donantes
     * - country-manager: Gestiona KPAs, usuarios, países, lectura de proyectos
     */
    public function run(): void
    {
        // Limpiar cache de permisos de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================================
        // ROL: Admin (Administrador del Sistema)
        // ============================================
        $admin = Role::where('name', 'admin')->where('guard_name', 'api')->first();

        if ($admin) {
            // Admin tiene wildcard permission = full access
            $wildcardPermission = Permission::where('name', '*:*')->first();
            if ($wildcardPermission) {
                $admin->givePermissionTo($wildcardPermission);
                $this->command->info('Admin: Wildcard permission (*:*) assigned');
            }
        } else {
            $this->command->warn('Role "admin" not found. Run RoleSeeder first.');
        }

        // ============================================
        // ROL: Project Manager (Gestor de Proyectos)
        // ============================================
        $projectManager = Role::where('name', 'project-manager')->where('guard_name', 'api')->first();

        if ($projectManager) {
            $projectManagerPermissions = [
                // Projects module (full access)
                'projects:read',
                'projects:write',

                // Programs module (full access)
                'programs:read',
                'programs:write',

                // Beneficiaries module (full access)
                'beneficiaries:read',
                'beneficiaries:write',

                // Donors module (read-only)
                'donors:read',

                // Indicators module (full access)
                'indicators:read',
                'indicators:write',

                // Agencies module (read-only)
                'agencies:read',
            ];

            $permissions = Permission::whereIn('name', $projectManagerPermissions)->get();
            $projectManager->syncPermissions($permissions);
            $this->command->info('Project-Manager: ' . $permissions->count() . ' permissions assigned');
        } else {
            $this->command->warn('Role "project-manager" not found. Run RoleSeeder first.');
        }

        // ============================================
        // ROL: Country Manager (Gestor de País)
        // ============================================
        $countryManager = Role::where('name', 'country-manager')->where('guard_name', 'api')->first();

        if ($countryManager) {
            $countryManagerPermissions = [
                // KPAs module (full access)
                'kpas:read',
                'kpas:write',

                // Programs module (full access)
                'programs:read',
                'programs:write',

                // Users module (full access)
                'users:read',
                'users:write',

                // Countries module (full access)
                'countries:read',
                'countries:write',

                // Projects module (read-only)
                'projects:read',

                // Donors module (read-only)
                'donors:read',

                // Beneficiaries module (read-only)
                'beneficiaries:read',

                // Agencies module (read-only)
                'agencies:read',
            ];

            $permissions = Permission::whereIn('name', $countryManagerPermissions)->get();
            $countryManager->syncPermissions($permissions);
            $this->command->info('Country-Manager: ' . $permissions->count() . ' permissions assigned');
        } else {
            $this->command->warn('Role "country-manager" not found. Run RoleSeeder first.');
        }

        $this->command->info('Role-Permission assignments completed');
    }
}
