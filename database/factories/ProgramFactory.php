<?php

namespace Database\Factories;

use App\Modules\Program\Domain\Program;
use App\Modules\Contact\Domain\Contact;
use App\Modules\ProgramState\Domain\ProgramState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->sentence(3),
            'description' => $this->faker->paragraph(),
            'banner_img' => 'program_banners/' . $this->faker->uuid() . '.jpg',
            'program_url' => $this->faker->url(),
            'contact_id' => Contact::factory(),
            'program_state_id' => ProgramState::factory(),
        ];
    }
}
