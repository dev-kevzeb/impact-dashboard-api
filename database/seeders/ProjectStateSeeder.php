<?php

namespace Database\Seeders;

use App\Modules\ProjectState\Domain\ProjectState;
use Illuminate\Database\Seeder;

class ProjectStateSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            ['state' => 'Active'],
            ['state' => 'Completed'],
        ];

        foreach ($states as $stateData) {
            ProjectState::firstOrCreate(
                ['state' => $stateData['state']],
                $stateData
            );
        }
    }
}
