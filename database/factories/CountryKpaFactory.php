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
        $country = Country::factory()->create();
        $kpa = Kpa::factory()->create();
        
        return [
            'id_country' => $country->id,
            'id_kpa' => $kpa->id,
        ];
    }
}

