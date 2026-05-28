<?php

namespace Tests\Feature;

use App\Modules\Role\Domain\Role;
use App\Modules\User\Domain\User;
use App\Modules\UserRole\Domain\UserRole;
use App\Modules\UserState\Domain\UserState;
use App\Notifications\AdminAccountEnabledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminUserCreationTest extends TestCase
{
    use RefreshDatabase;

    private const CREATE_ADMIN_URL = '/api/v1/users/admins';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\UserStateSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_admin_can_create_admin_user_and_send_enabled_email(): void
    {
        Notification::fake();

        $payload = [
            'name' => 'Second Administrator',
            'email' => 'second-admin@test.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ];

        $response = $this->postJson(self::CREATE_ADMIN_URL, $payload, $this->authHeaders('admin'));

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Admin user created successfully. The account is active and ready to login.',
            ])
            ->assertJsonPath('data.email', 'second-admin@test.com')
            ->assertJsonPath('data.roles.0.name', 'admin')
            ->assertJsonPath('data.userState.name', 'active');

        $createdAdmin = User::where('email', 'second-admin@test.com')->firstOrFail();
        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $activeState = UserState::where('name', 'active')->firstOrFail();

        $this->assertDatabaseHas('user', [
            'id' => $createdAdmin->id,
            'user_state_id' => $activeState->id,
        ]);

        $this->assertNotNull($createdAdmin->email_verified_at);

        $this->assertDatabaseHas('user_role', [
            'user_id' => $createdAdmin->id,
            'role_id' => $adminRole->id,
        ]);

        // Admin must not have a country assignment by default.
        $adminUserRole = UserRole::where('user_id', $createdAdmin->id)
            ->where('role_id', $adminRole->id)
            ->firstOrFail();

        $this->assertDatabaseMissing('country_user_role', [
            'user_role_id' => $adminUserRole->id,
        ]);

        Notification::assertSentTo($createdAdmin, AdminAccountEnabledNotification::class);
    }

    public function test_non_admin_cannot_create_admin_user(): void
    {
        $payload = [
            'name' => 'Unauthorized Admin Create',
            'email' => 'unauthorized-admin-create@test.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ];

        // Country manager has users:write in current permissions, so middleware allows request.
        // Service-level admin-role check must block the action.
        $response = $this->postJson(self::CREATE_ADMIN_URL, $payload, $this->authHeaders('country-manager'));

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Insufficient permissions. Required scope: users:write',
            ]);

        $this->assertDatabaseMissing('user', [
            'email' => 'unauthorized-admin-create@test.com',
        ]);
    }
}
