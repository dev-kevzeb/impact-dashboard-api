<?php

namespace Database\Seeders;

use App\Modules\ProgramState\Domain\ProgramState;
use Illuminate\Database\Seeder;

class ProgramStateSeeder extends Seeder
{
    /**
     * Programs are created with "Inactive" state by default.
     */
    public function run(): void
    {
        $states = [
            ['name' => 'Inactive'],
            ['name' => 'Active'],
            ['name' => 'Completed'],
        ];

        foreach ($states as $state) {
            ProgramState::firstOrCreate(['name' => $state['name']], $state);
        }
    }
}
