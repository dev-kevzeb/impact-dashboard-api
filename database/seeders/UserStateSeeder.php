<?php

namespace Database\Seeders;

use App\Modules\UserState\Domain\UserState;
use Illuminate\Database\Seeder;

class UserStateSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            ['id' => 1, 'name' => 'unverified'],  // Email no verificado
            ['id' => 2, 'name' => 'pending'],     // Email verificado, esperando admin
            ['id' => 3, 'name' => 'active'],      // Aprobado, puede hacer login
            ['id' => 4, 'name' => 'inactive'],    // Desactivado
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
