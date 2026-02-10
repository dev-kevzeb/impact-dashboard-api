<?php

namespace Database\Seeders;

use App\Modules\User\Domain\User;
use App\Modules\UserState\Domain\UserState;
use Illuminate\Database\Seeder;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $activeState = UserState::where('name', 'active')->first();

        if (!$activeState) {
            return;
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@pacific.com'],
            [
                'name' => 'Administrator',
                'password' => 'admin123',
                'user_state_id' => $activeState->id
            ]
        );

        $adminRole = \App\Modules\Role\Domain\Role::where('name', 'admin')->first();
        if ($adminRole) {
            \DB::table('user_role')->updateOrInsert([
                'user_id' => $admin->id,
                'role_id' => $adminRole->id
            ]);
        }
    }
}
