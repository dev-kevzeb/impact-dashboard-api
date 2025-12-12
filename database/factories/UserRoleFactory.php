<?php

namespace Database\Factories;

use App\Modules\UserRole\Domain\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserRole>
 */
class UserRoleFactory extends Factory
{
    protected $model = UserRole::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'admin',
                'country_manager',
                'project_manager',
                'data_analyst',
                'auditor',
                'viewer',
                'editor',
                'super_admin'
            ]),
        ];
    }
}
