<?php

namespace Database\Seeders;

use App\Modules\User\Domain\User;
use App\Modules\UserState\Domain\UserState;
use Illuminate\Database\Seeder;

/**
 * Test Users Seeder
 * 
 * Creates test users for frontend development with known credentials.
 * Frontend team can use these to login and get tokens without implementing register.
 */
class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $activeState = UserState::where('name', 'active')->first();

        if (!$activeState) {
            $this->command->error('UserState "active" not found. Run UserStateSeeder first.');
            return;
        }

        // Admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@pacific.com'],
            [
                'name' => 'Admin User',
                'password' => 'admin123',  // Will be hashed by mutator
                'user_state_id' => $activeState->id
            ]
        );

        // Assign admin role if exists
        $adminRole = \App\Modules\Role\Domain\Role::where('name', 'admin')->first();
        if ($adminRole) {
            \DB::table('user_role')->updateOrInsert([
                'user_id' => $admin->id,
                'role_id' => $adminRole->id
            ]);
        }

        $this->command->info('Created: admin@pacific.com / admin123 (Role: admin)');

        // Project Manager user
        $projectManager = User::updateOrCreate(
            ['email' => 'project@pacific.com'],
            [
                'name' => 'Project Manager',
                'password' => 'project123',
                'user_state_id' => $activeState->id
            ]
        );

        $pmRole = \App\Modules\Role\Domain\Role::where('name', 'project-manager')->first();
        if ($pmRole) {
            \DB::table('user_role')->updateOrInsert([
                'user_id' => $projectManager->id,
                'role_id' => $pmRole->id
            ]);
        }

        $this->command->info('Created: project@pacific.com / project123 (Role: project-manager)');

        // Country Manager user
        $countryManager = User::updateOrCreate(
            ['email' => 'country@pacific.com'],
            [
                'name' => 'Country Manager',
                'password' => 'country123',
                'user_state_id' => $activeState->id
            ]
        );

        $cmRole = \App\Modules\Role\Domain\Role::where('name', 'country-manager')->first();
        if ($cmRole) {
            \DB::table('user_role')->updateOrInsert([
                'user_id' => $countryManager->id,
                'role_id' => $cmRole->id
            ]);
        }

        $this->command->info('Created: country@pacific.com / country123 (Role: country-manager)');

        $this->command->info('');
        $this->command->info('TEST USERS CREATED - Frontend can now login with:');
        $this->command->info('   • admin@pacific.com / admin123 (full access)');
        $this->command->info('   • project@pacific.com / project123 (projects, programs, beneficiaries)');
        $this->command->info('   • country@pacific.com / country123 (kpas, users, programs)');
    }
}
