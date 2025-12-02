<?php

namespace Database\Factories;

use App\Modules\Country\Domain\Country;
use App\Modules\Currency\Domain\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->country(),  
            'currency_id' => Currency::factory(),
        ];
    }
}
