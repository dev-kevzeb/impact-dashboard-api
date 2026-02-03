<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Módulos del sistema:
     * - donors
     * - beneficiaries
     * - projects
     * - programs
     * - kpas
     * - users
     * - countries
     * - agencies
     * - indicators
     */
    public function run(): void
    {
        // Limpiar cache de permisos de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definir permisos por módulo
        $permissions = [
            // ============================================
            // PERMISO ESPECIAL: Wildcard Admin
            // ============================================
            [
                'name' => '*:*',
                'scope' => 'all',
                'module' => 'admin',
                'description' => 'Full system access (God mode) - Admin only',
            ],

            // ============================================
            // MÓDULO: Donors (Donantes)
            // ============================================
            [
                'name' => 'donors:read',
                'scope' => 'donors',
                'module' => 'Donor',
                'description' => 'View donors list and details',
            ],
            [
                'name' => 'donors:write',
                'scope' => 'donors',
                'module' => 'Donor',
                'description' => 'Create and edit donors',
            ],

            // ============================================
            // MÓDULO: Beneficiaries (Beneficiarios)
            // ============================================
            [
                'name' => 'beneficiaries:read',
                'scope' => 'beneficiaries',
                'module' => 'Beneficiary',
                'description' => 'View beneficiaries list and details',
            ],
            [
                'name' => 'beneficiaries:write',
                'scope' => 'beneficiaries',
                'module' => 'Beneficiary',
                'description' => 'Create and edit beneficiaries',
            ],

            // ============================================
            // MÓDULO: Projects (Proyectos)
            // ============================================
            [
                'name' => 'projects:read',
                'scope' => 'projects',
                'module' => 'Project',
                'description' => 'View projects list and details',
            ],
            [
                'name' => 'projects:write',
                'scope' => 'projects',
                'module' => 'Project',
                'description' => 'Create and edit projects',
            ],

            // ============================================
            // MÓDULO: Programs (Programas)
            // ============================================
            [
                'name' => 'programs:read',
                'scope' => 'programs',
                'module' => 'Program',
                'description' => 'View programs list and details',
            ],
            [
                'name' => 'programs:write',
                'scope' => 'programs',
                'module' => 'Program',
                'description' => 'Create and edit programs',
            ],

            // ============================================
            // MÓDULO: KPAs (Key Performance Areas)
            // ============================================
            [
                'name' => 'kpas:read',
                'scope' => 'kpas',
                'module' => 'Kpa',
                'description' => 'View KPAs list and details',
            ],
            [
                'name' => 'kpas:write',
                'scope' => 'kpas',
                'module' => 'Kpa',
                'description' => 'Create and edit KPAs',
            ],

            // ============================================
            // MÓDULO: Users (Usuarios)
            // ============================================
            [
                'name' => 'users:read',
                'scope' => 'users',
                'module' => 'User',
                'description' => 'View users list and details',
            ],
            [
                'name' => 'users:write',
                'scope' => 'users',
                'module' => 'User',
                'description' => 'Create and edit users',
            ],

            // ============================================
            // MÓDULO: Countries (Países)
            // ============================================
            [
                'name' => 'countries:read',
                'scope' => 'countries',
                'module' => 'Country',
                'description' => 'View countries list and details',
            ],
            [
                'name' => 'countries:write',
                'scope' => 'countries',
                'module' => 'Country',
                'description' => 'Create and edit countries',
            ],

            // ============================================
            // MÓDULO: Agencies (Agencias)
            // ============================================
            [
                'name' => 'agencies:read',
                'scope' => 'agencies',
                'module' => 'Agency',
                'description' => 'View agencies list and details',
            ],
            [
                'name' => 'agencies:write',
                'scope' => 'agencies',
                'module' => 'Agency',
                'description' => 'Create and edit agencies',
            ],

            // ============================================
            // MÓDULO: Indicators (Indicadores)
            // ============================================
            [
                'name' => 'indicators:read',
                'scope' => 'indicators',
                'module' => 'Indicator',
                'description' => 'View indicators list and details',
            ],
            [
                'name' => 'indicators:write',
                'scope' => 'indicators',
                'module' => 'Indicator',
                'description' => 'Create and edit indicators',
            ],

            // ============================================
            // MÓDULO: Roles (Roles y Permisos)
            // ============================================
            [
                'name' => 'roles:read',
                'scope' => 'roles',
                'module' => 'Role',
                'description' => 'View roles and permissions',
            ],
            [
                'name' => 'roles:write',
                'scope' => 'roles',
                'module' => 'Role',
                'description' => 'Manage roles and permissions',
            ],
        ];

        // Crear permisos en la base de datos
        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission['name'],
                'guard_name' => 'api',
                'scope' => $permission['scope'],
                'module' => $permission['module'],
                'description' => $permission['description'],
            ]);
        }

        $this->command->info('✅ ' . count($permissions) . ' permissions created successfully');
    }
}
