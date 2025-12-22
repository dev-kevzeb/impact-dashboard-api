<?php

namespace Database\Factories;

use App\Modules\CountryKpa\Domain\CountryKpa;
use App\Modules\CountryKpaUser\Domain\CountryKpaUser;
use App\Modules\User\Domain\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CountryKpaUser>
 */
class CountryKpaUserFactory extends Factory
{
    protected $model = CountryKpaUser::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_kpa_id' => CountryKpa::factory(),
            'user_id' => User::factory(),
        ];
    }
}
