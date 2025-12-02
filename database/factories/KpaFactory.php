<?php

namespace Database\Factories;

use App\Modules\Kpa\Domain\Kpa;
use Illuminate\Database\Eloquent\Factories\Factory;

class KpaFactory extends Factory
{
    protected $model = Kpa::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence(2),
            'implementation' => $this->faker->numberBetween(0, 100), 
        ];
    }
}