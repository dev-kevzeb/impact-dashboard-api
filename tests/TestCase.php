<?php

namespace Tests;

use App\Modules\User\Domain\User;
use App\Modules\UserState\Domain\UserState;
use App\Modules\Role\Domain\Role;
use App\Modules\Country\Domain\Country;
use App\Modules\Currency\Domain\Currency;
use App\Modules\CountryUserRole\Domain\CountryUserRole;
use App\Modules\UserRole\Domain\UserRole;
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

        // Find or create test user (reusable across multiple authHeaders() calls in same test)
        $user = User::firstOrCreate(
            ['email' => 'test@pacific.com'],
            [
                'name' => 'Test Admin User',
                'password' => 'password123',
                'user_state_id' => $activeState->id
            ]
        );

        // Assign role (check if not already assigned to avoid duplicate entry errors)
        $role = Role::where('name', $roleName)->firstOrFail();
        $roleExists = \DB::table('user_role')
            ->where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->exists();

        if (!$roleExists) {
            \DB::table('user_role')->insert([
                'user_id' => $user->id,
                'role_id' => $role->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

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

    /**
     * Authenticate a user and ensure they have a CountryUserRole assigned.
     * Required for any endpoint that calls $request->user()->getCountryUserRole().
     *
     * @param string $roleName
     * @return array{headers: array, countryUserRole: CountryUserRole}
     */
    protected function authHeadersWithCountry(string $roleName = 'project-manager'): array
    {
        $token = $this->authenticateUser($roleName);

        $user = User::where('email', 'test@pacific.com')->firstOrFail();

        // Ensure a country exists (reuse or create)
        $currency = Currency::firstOrCreate(['code' => 'USD', 'name' => 'US Dollar']);
        $country  = Country::firstOrCreate(
            ['name' => 'Test Country'],
            ['currency_id' => $currency->id, 'active' => true]
        );

        // Get the UserRole just created/reused in authenticateUser()
        $role     = Role::where('name', $roleName)->firstOrFail();
        $userRole = UserRole::where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->firstOrFail();

        // Create CountryUserRole if it does not exist
        $countryUserRole = CountryUserRole::firstOrCreate([
            'country_id'   => $country->id,
            'user_role_id' => $userRole->id,
        ]);

        return [
            'headers'          => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
            ],
            'countryUserRole'  => $countryUserRole,
        ];
    }
}
