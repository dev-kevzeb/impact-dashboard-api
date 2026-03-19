<?php

namespace Tests\Feature;

use App\Notifications\AccountApprovedNotification;
use App\Notifications\AccountRejectedNotification;
use App\Modules\User\Domain\User;
use App\Modules\Role\Domain\Role;
use App\Modules\UserState\Domain\UserState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserUnverifiedTest extends TestCase
{
    use RefreshDatabase;

    private const UNVERIFIED_URL = '/api/v1/users/unverified';
    private const REJECT_URL = '/api/v1/users';

    private UserState $unverifiedState;
    private UserState $pendingState;
    private UserState $activeState;
    private Role $projectManagerRole;

    protected function setUp(): void
    {
        parent::setUp();

        // IMPORTANT: Execute seeders BEFORE getting entity references
        // This prevents ID conflicts when authHeaders() executes the same seeders
        // RoleSeeder uses fixed IDs (1=admin, 2=project-manager, 3=country-manager)
        // using Role::factory()->create() would create roles with conflicting IDs
        $this->seed(\Database\Seeders\UserStateSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        // Get references to seeded entities
        $this->unverifiedState = UserState::where('name', 'unverified')->firstOrFail();
        $this->pendingState = UserState::where('name', 'pending')->firstOrFail();
        $this->activeState = UserState::where('name', 'active')->firstOrFail();
        $this->projectManagerRole = Role::where('name', 'project-manager')->firstOrFail();
    }

    public function test_admin_can_get_unverified_users(): void
    {
        // Create 5 unverified users
        User::factory()->count(5)->create([
            'user_state_id' => $this->unverifiedState->id
        ]);

        // Create users with other states (should NOT appear)
        User::factory()->count(2)->create([
            'user_state_id' => $this->pendingState->id
        ]);
        User::factory()->count(2)->create([
            'user_state_id' => $this->activeState->id
        ]);

        $response = $this->getJson(self::UNVERIFIED_URL, $this->authHeaders());

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Unverified users retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'users',
                    'total',
                    'per_page',
                    'current_page',
                    'last_page'
                ]
            ])
            ->assertJsonCount(5, 'data.users')
            ->assertJson([
                'data' => [
                    'total' => 5,
                ]
            ]);
    }

    public function test_unverified_users_excludes_other_states(): void
    {
        // Create 3 unverified users
        User::factory()->count(3)->create([
            'user_state_id' => $this->unverifiedState->id
        ]);

        // Create users with other states
        User::factory()->count(3)->create([
            'user_state_id' => $this->pendingState->id
        ]);
        User::factory()->count(3)->create([
            'user_state_id' => $this->activeState->id
        ]);

        $response = $this->getJson(self::UNVERIFIED_URL, $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(3, 'data.users');

        // Verify ALL returned users are in unverified state
        $returnedUsers = $response->json('data.users');
        foreach ($returnedUsers as $user) {
            $this->assertEquals('unverified', $user['userState']['name']);
        }
    }

    public function test_unverified_users_pagination_works(): void
    {
        // Create 15 unverified users
        User::factory()->count(15)->create([
            'user_state_id' => $this->unverifiedState->id
        ]);

        // Request page 1 with 5 items per page
        $response = $this->getJson(self::UNVERIFIED_URL . '?per_page=5&page=1', $this->authHeaders());

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'total' => 15,
                    'per_page' => 5,
                    'current_page' => 1,
                    'last_page' => 3
                ]
            ])
            ->assertJsonCount(5, 'data.users');

        // Request page 2
        $responsePage2 = $this->getJson(self::UNVERIFIED_URL . '?per_page=5&page=2', $this->authHeaders());

        $responsePage2->assertOk()
            ->assertJson([
                'data' => [
                    'current_page' => 2,
                ]
            ])
            ->assertJsonCount(5, 'data.users');
    }

    public function test_can_reject_unverified_user(): void
    {
        Notification::fake();

        // Create unverified user
        $unverifiedUser = User::factory()->create([
            'name' => 'Unverified User',
            'email' => 'unverified@test.com',
            'user_state_id' => $this->unverifiedState->id
        ]);

        // Assign project-manager role
        $unverifiedUser->roles()->attach($this->projectManagerRole->id);

        // Verify user exists
        $this->assertDatabaseHas('user', [
            'id' => $unverifiedUser->id,
            'email' => 'unverified@test.com'
        ]);

        // Reject (delete) the unverified user
        $response = $this->deleteJson(self::REJECT_URL . "/{$unverifiedUser->id}/reject", [], $this->authHeaders());

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'User registration rejected and deleted successfully.'
            ]);

        // Verify user was deleted (hard delete)
        $this->assertDatabaseMissing('user', [
            'id' => $unverifiedUser->id,
            'email' => 'unverified@test.com'
        ]);

        Notification::assertSentTo($unverifiedUser, AccountRejectedNotification::class);
    }

    public function test_can_reject_pending_user(): void
    {
        Notification::fake();

        // Create pending user
        $pendingUser = User::factory()->create([
            'name' => 'Pending User',
            'email' => 'pending@test.com',
            'user_state_id' => $this->pendingState->id
        ]);

        // Assign project-manager role
        $pendingUser->roles()->attach($this->projectManagerRole->id);

        // Verify user exists
        $this->assertDatabaseHas('user', [
            'id' => $pendingUser->id,
            'email' => 'pending@test.com'
        ]);

        // Reject (delete) the pending user
        $response = $this->deleteJson(self::REJECT_URL . "/{$pendingUser->id}/reject", [], $this->authHeaders());

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'User registration rejected and deleted successfully.'
            ]);

        // Verify user was deleted (hard delete)
        $this->assertDatabaseMissing('user', [
            'id' => $pendingUser->id,
            'email' => 'pending@test.com'
        ]);

        Notification::assertSentTo($pendingUser, AccountRejectedNotification::class);
    }

    public function test_cannot_reject_active_user(): void
    {
        // Create active user
        $activeUser = User::factory()->create([
            'name' => 'Active User',
            'email' => 'active@test.com',
            'user_state_id' => $this->activeState->id
        ]);

        // Assign project-manager role
        $activeUser->roles()->attach($this->projectManagerRole->id);

        // Try to reject active user (should fail)
        $response = $this->deleteJson(self::REJECT_URL . "/{$activeUser->id}/reject", [], $this->authHeaders());

        $response->assertStatus(400)
            ->assertJson([
                'success' => false
            ])
            ->assertJsonFragment([
                'message' => 'Cannot reject user. Only unverified/pending users can be rejected. Current state: active'
            ]);

        // Verify user still exists
        $this->assertDatabaseHas('user', [
            'id' => $activeUser->id,
            'email' => 'active@test.com'
        ]);
    }

    public function test_unverified_users_ordered_by_created_at_desc(): void
    {
        // Create unverified users with different timestamps
        $user1 = User::factory()->create([
            'name' => 'User 1',
            'user_state_id' => $this->unverifiedState->id,
            'created_at' => now()->subDays(3)
        ]);

        $user2 = User::factory()->create([
            'name' => 'User 2',
            'user_state_id' => $this->unverifiedState->id,
            'created_at' => now()->subDays(1)
        ]);

        $user3 = User::factory()->create([
            'name' => 'User 3',
            'user_state_id' => $this->unverifiedState->id,
            'created_at' => now()->subDays(2)
        ]);

        $response = $this->getJson(self::UNVERIFIED_URL, $this->authHeaders());

        $response->assertOk();

        $returnedUsers = $response->json('data.users');

        // Verify order: most recent first (User 2, User 3, User 1)
        $this->assertEquals('User 2', $returnedUsers[0]['name']);
        $this->assertEquals('User 3', $returnedUsers[1]['name']);
        $this->assertEquals('User 1', $returnedUsers[2]['name']);
    }

    public function test_can_approve_pending_user_and_send_account_approved_email(): void
    {
        Notification::fake();

        $pendingUser = User::factory()->create([
            'name' => 'Pending Approval User',
            'email' => 'pending-approve@test.com',
            'user_state_id' => $this->pendingState->id,
        ]);

        $pendingUser->roles()->attach($this->projectManagerRole->id);

        $response = $this->postJson(self::REJECT_URL . "/{$pendingUser->id}/approve", [], $this->authHeaders());

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'User approved successfully. They can now login.',
            ]);

        $this->assertDatabaseHas('user', [
            'id' => $pendingUser->id,
            'user_state_id' => $this->activeState->id,
        ]);

        Notification::assertSentTo($pendingUser, AccountApprovedNotification::class);
    }
}
