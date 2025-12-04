<?php

namespace Database\Factories;

use App\Modules\CountryKpa\Domain\CountryKpa;
use App\Modules\Country\Domain\Country;
use App\Modules\Kpa\Domain\Kpa;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryKpaFactory extends Factory
{
    protected $model = CountryKpa::class;

    public function definition(): array
    {
        return [
            'id_country' => Country::factory(),
            'id_kpa' => Kpa::factory(),
        ];
    }
}

