<?php

namespace Database\Factories;

use App\Modules\User\Domain\User;
use App\Modules\UserState\Domain\UserState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password123', // Will be auto-hashed by mutator
            'user_state_id' => UserState::factory(),
        ];
    }
}
