<?php

namespace Database\Factories;

use App\Modules\Program\Domain\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $endDate = $this->faker->dateTimeBetween($startDate, '+2 years');
        
        return [
            'name' => $this->faker->unique()->sentence(3),
            'description' => $this->faker->paragraph(),
            'banner_img' => 'program_banners/' . $this->faker->uuid() . '.jpg',
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'program_url' => $this->faker->url(),
            'contact_id' => 1,
            'beneficiary_id' => 1,
            'program_state_id' => 1,
            'country_id' => 1,
            'agency_id' => 1,
        ];
    }
}
