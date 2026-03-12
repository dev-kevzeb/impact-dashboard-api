<?php

namespace Database\Factories;

use App\Modules\ProgramCountryUserRole\Domain\ProgramCountryUserRole;
use App\Modules\Program\Domain\Program;
use App\Modules\CountryUserRole\Domain\CountryUserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgramCountryUserRole>
 */
class ProgramCountryUserRoleFactory extends Factory
{
    protected $model = ProgramCountryUserRole::class;

    public function definition(): array
    {
        return [
            'program_id'           => Program::factory(),
            'country_user_role_id' => CountryUserRole::factory(),
        ];
    }
}
