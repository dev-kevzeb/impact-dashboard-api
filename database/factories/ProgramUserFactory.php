<?php

namespace Database\Factories;

use App\Modules\ProgramUser\Domain\ProgramUser;
use App\Modules\Program\Domain\Program;
use App\Modules\CountryKpaUser\Domain\CountryKpaUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgramUser>
 */
class ProgramUserFactory extends Factory
{
    protected $model = ProgramUser::class;

    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'country_kpa_user_id' => CountryKpaUser::factory(),
        ];
    }
}
