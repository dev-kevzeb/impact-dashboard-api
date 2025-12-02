<?php

namespace Database\Factories;

use App\Modules\ProjectState\Domain\ProjectState;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectStateFactory extends Factory
{
    protected $model = ProjectState::class;

    public function definition(): array
    {
        $states = [
            'pendiente',
            'en progreso',
            'completado',
            'en revisión',
            'cancelado',
            'aprobado',
        ];

        return [
            'state' => $this->faker->randomElement($states),
        ];
    }
}
