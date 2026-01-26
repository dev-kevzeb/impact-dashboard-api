<?php

namespace Database\Seeders;

use App\Modules\Role\Domain\Role;
use Illuminate\Database\Seeder;

/**
 * Role Seeder
 * 
 * Creates the three main system roles:
 * - admin: Full system access
 * - project-manager: Manages projects, programs, beneficiaries
 * - country-manager: Manages KPAs, users, country-level operations
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'System administrator with full access to all modules'
            ],
            [
                'name' => 'project-manager',
                'description' => 'Manages projects, programs, beneficiaries, and indicators'
            ],
            [
                'name' => 'country-manager',
                'description' => 'Manages KPAs, countries, users, and program assignments'
            ]
        ];

        foreach ($roles as $roleData) {
            $role = Role::updateOrCreate(
                ['name' => $roleData['name']],
                ['name' => $roleData['name']]
            );

            $this->command->info("Created role: {$role->name}");
        }

        $this->command->info('');
        $this->command->info('ROLES CREATED - System ready for user-role assignments');
    }
}
