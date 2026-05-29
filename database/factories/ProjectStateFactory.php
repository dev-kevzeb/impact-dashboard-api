<?php

namespace Database\Factories;

use App\Modules\ProjectState\Domain\ProjectState;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectStateFactory extends Factory
{
    protected $model = ProjectState::class;

    public function definition(): array
    {
        return [
            'state' => $this->faker->unique()->words(2, true),
        ];
    }
}
