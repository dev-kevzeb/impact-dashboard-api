<?php

namespace Database\Factories;

use App\Modules\UserState\Domain\UserState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserState>
 */
class UserStateFactory extends Factory
{
    protected $model = UserState::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'active',
                'inactive',
                'suspended',
                'pending',
                'blocked',
                'archived'
            ]) . ' ' . $this->faker->numberBetween(1, 100000),
        ];
    }
}
