<?php

namespace Database\Factories;

use App\Modules\Measure\Domain\Measure;
use App\Modules\StrategicOutput\Domain\StrategicOutput;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeasureFactory extends Factory
{
    protected $model = Measure::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->unique()->words(2, true)),
            'strategic_output_id' => StrategicOutput::factory(),  
        ];
    }
}