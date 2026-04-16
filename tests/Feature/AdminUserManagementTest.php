<?php

namespace Tests\Feature;

use App\Modules\Role\Domain\Role;
use App\Modules\User\Domain\User;
use App\Modules\UserState\Domain\UserState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private const LIST_ADMINS_URL = '/api/v1/users/admins';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\UserStateSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_admin_can_list_admins_excluding_authenticated_user(): void
    {
        $activeState = UserState::where('name', 'active')->firstOrFail();

        $adminOne = User::factory()->create([
            'name' => 'Admin One',
            'email' => 'admin-one@test.com',
            'user_state_id' => $activeState->id,
            'email_verified_at' => now(),
        ]);
        $adminOne->assignRole('admin');

        $adminTwo = User::factory()->create([
            'name' => 'Admin Two',
            'email' => 'admin-two@test.com',
            'user_state_id' => $activeState->id,
            'email_verified_at' => now(),
        ]);
        $adminTwo->assignRole('admin');

        $response = $this->getJson(self::LIST_ADMINS_URL, $this->authHeaders('admin'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Admin users retrieved successfully',
            ])
            ->assertJsonPath('data.total', 2)
            ->assertJsonMissing(['email' => 'test@pacific.com'])
            ->assertJsonFragment(['email' => 'admin-one@test.com'])
            ->assertJsonFragment(['email' => 'admin-two@test.com']);
    }

    public function test_non_admin_cannot_list_admin_accounts(): void
    {
        $response = $this->getJson(self::LIST_ADMINS_URL, $this->authHeaders('country-manager'));

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Only admin users can list admin accounts.',
            ]);
    }

    public function test_admin_can_delete_another_admin_when_more_than_one_active(): void
    {
        $activeState = UserState::where('name', 'active')->firstOrFail();

        $targetAdmin = User::factory()->create([
            'name' => 'Delete Me Admin',
            'email' => 'delete-admin@test.com',
            'user_state_id' => $activeState->id,
            'email_verified_at' => now(),
        ]);
        $targetAdmin->assignRole('admin');

        $response = $this->deleteJson('/api/v1/users/admins/' . $targetAdmin->id, [], $this->authHeaders('admin'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Admin user deleted successfully.',
            ]);

        $this->assertDatabaseMissing('user', ['id' => $targetAdmin->id]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $actor = User::where('email', 'test@pacific.com')->first();
        if (!$actor) {
            $this->authHeaders('admin');
            $actor = User::where('email', 'test@pacific.com')->firstOrFail();
        }

        $response = $this->deleteJson('/api/v1/users/admins/' . $actor->id, [], $this->authHeaders('admin'));

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'You cannot delete your own admin account.',
            ]);

        $this->assertDatabaseHas('user', ['id' => $actor->id]);
    }

    public function test_cannot_delete_last_active_admin_account(): void
    {
        $activeState = UserState::where('name', 'active')->firstOrFail();
        $inactiveState = UserState::where('name', 'inactive')->firstOrFail();

        // Ensure authenticated admin exists and has admin role.
        $this->authHeaders('admin');
        $actor = User::where('email', 'test@pacific.com')->firstOrFail();

        // Make actor inactive so it does not count as active admin.
        $actor->user_state_id = $inactiveState->id;
        $actor->save();

        $soleActiveAdmin = User::factory()->create([
            'name' => 'Sole Active Admin',
            'email' => 'sole-active-admin@test.com',
            'user_state_id' => $activeState->id,
            'email_verified_at' => now(),
        ]);
        $soleActiveAdmin->assignRole('admin');

        $response = $this->deleteJson('/api/v1/users/admins/' . $soleActiveAdmin->id, [], $this->authHeaders('admin'));

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot delete the last active admin account. At least one active admin must remain.',
            ]);

        $this->assertDatabaseHas('user', ['id' => $soleActiveAdmin->id]);
    }

    public function test_non_admin_cannot_delete_admin_account(): void
    {
        $activeState = UserState::where('name', 'active')->firstOrFail();

        $targetAdmin = User::factory()->create([
            'name' => 'Protected Admin',
            'email' => 'protected-admin@test.com',
            'user_state_id' => $activeState->id,
            'email_verified_at' => now(),
        ]);
        $targetAdmin->assignRole('admin');

        $response = $this->deleteJson('/api/v1/users/admins/' . $targetAdmin->id, [], $this->authHeaders('country-manager'));

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Only admin users can delete admin accounts.',
            ]);

        $this->assertDatabaseHas('user', ['id' => $targetAdmin->id]);
    }
}
