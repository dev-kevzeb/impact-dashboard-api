<?php

namespace Database\Factories;

use App\Modules\ProgramState\Domain\ProgramState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgramState>
 */
class ProgramStateFactory extends Factory
{
    protected $model = ProgramState::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Active',
                'Inactive',
                'Planned',
                'Completed',
                'On Hold',
                'Cancelled',
                'Under Review',
                'Approved',
                'In Progress',
                'Suspended'
            ]),
        ];
    }
}
