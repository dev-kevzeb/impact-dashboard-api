<?php

namespace Tests;

use App\Modules\User\Domain\User;
use App\Modules\UserState\Domain\UserState;
use App\Modules\Role\Domain\Role;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create an authenticated user and return JWT token
     *
     * @param string $roleName Role to assign (default: admin)
     * @return string JWT token
     */
    protected function authenticateUser(string $roleName = 'admin'): string
    {
        // Ensure states and roles exist
        $this->seed(\Database\Seeders\UserStateSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        // Get active state
        $activeState = UserState::where('name', 'active')->firstOrFail();

        // Create test user
        $user = User::factory()->create([
            'email' => 'test@pacific.com',
            'password' => 'password123',
            'user_state_id' => $activeState->id
        ]);

        // Assign role
        $role = Role::where('name', $roleName)->firstOrFail();
        \DB::table('user_role')->insert([
            'user_id' => $user->id,
            'role_id' => $role->id
        ]);

        // Generate JWT token
        $token = auth('api')->login($user);

        return $token;
    }

    /**
     * Get authorization header with JWT token
     *
     * @param string $roleName Role to assign (default: admin)
     * @return array Headers array
     */
    protected function authHeaders(string $roleName = 'admin'): array
    {
        $token = $this->authenticateUser($roleName);

        return [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];
    }
}
