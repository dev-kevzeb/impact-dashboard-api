<?php

namespace Database\Factories;

use App\Modules\Country\Domain\Country;
use App\Modules\CountryUserRole\Domain\CountryUserRole;
use App\Modules\UserRole\Domain\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CountryUserRole>
 */
class CountryUserRoleFactory extends Factory
{
    protected $model = CountryUserRole::class;

    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'user_role_id' => UserRole::factory(),
        ];
    }
}
