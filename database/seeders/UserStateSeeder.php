<?php

namespace Database\Seeders;

use App\Modules\UserState\Domain\UserState;
use Illuminate\Database\Seeder;

class UserStateSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            ['id' => 1, 'name' => 'pending'],
            ['id' => 2, 'name' => 'active'],
            ['id' => 3, 'name' => 'inactive'],
        ];

        foreach ($states as $state) {
            \DB::table('user_state')->updateOrInsert(
                ['id' => $state['id']],
                array_merge($state, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }
    }
}
