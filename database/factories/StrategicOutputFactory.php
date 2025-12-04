<?php

namespace Database\Factories;

use App\Modules\StrategicOutput\Domain\StrategicOutput;
use App\Modules\CountryKpa\Domain\CountryKpa;
use Illuminate\Database\Eloquent\Factories\Factory;

class StrategicOutputFactory extends Factory
{
    protected $model = StrategicOutput::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->sentence(3),
            'id_ck' => CountryKpa::factory(),
        ];
    }
}
