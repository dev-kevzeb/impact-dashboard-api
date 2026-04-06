<?php

namespace Database\Factories;

use App\Modules\IndicatorType\Domain\IndicatorType;

use Illuminate\Database\Eloquent\Factories\Factory;

class IndicatorTypeFactory extends Factory
{
    protected $model = IndicatorType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            'is_bottom_up' => true,
        ];
    }
}