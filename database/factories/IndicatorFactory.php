<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Modules\Indicator\Domain\Indicator;
use App\Modules\IndicatorType\Domain\IndicatorType;
use App\Modules\Measure\Domain\Measure;

class IndicatorFactory extends Factory
{
    protected $model = Indicator::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->words(3, true)),
            'type_id' => IndicatorType::factory(),
            'measure_id' => Measure::factory(),
            'target' => $this->faker->numberBetween(1, 100),
        ];
    }
}
