<?php

namespace Database\Factories;

use App\Modules\Role\Domain\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserRole>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'admin',
                'country_manager',
                'project_manager',
                'data_analyst',
                'auditor',
                'viewer',
                'editor',
                'super_admin'
            ]) . ' ' . $this->faker->numberBetween(1, 100000),
        ];
    }
}
