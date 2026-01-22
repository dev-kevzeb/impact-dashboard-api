<?php

namespace Database\Seeders;

use App\Modules\UserState\Domain\UserState;
use Illuminate\Database\Seeder;

class UserStateSeeder extends Seeder
{
    /**
     * Seed the user states table
     * 
     * States:
     * - pending: User registered, waiting for admin approval
     * - active: User approved, can login
     * - inactive: User suspended/deactivated
     */
    public function run(): void
    {
        $states = [
            ['name' => 'pending'],
            ['name' => 'active'],
            ['name' => 'inactive'],
        ];

        foreach ($states as $state) {
            UserState::firstOrCreate(['name' => $state['name']], $state);
        }

        $this->command->info('User states seeded: pending, active, inactive');
    }
}
