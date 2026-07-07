<?php

namespace Tests\Feature;

use App\Modules\Role\Domain\Role;
use App\Modules\User\Domain\User;
use App\Modules\UserRole\Domain\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/user_roles';
    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->headers = $this->authHeaders('admin');
        $user = User::where('email', 'test@pacific.com')->first();
        $user->givePermissionTo(['user_roles:read', 'user_roles:write']);
        $this->headers['Authorization'] = 'Bearer ' . auth('api')->login($user);
    }

    /**
     * Test retrieving all assignments
     */
    public function test_can_list_all_assignments(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $assignment = UserRole::factory()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);

        $response = $this->getJson(self::BASE_URL, $this->headers);

        $response->assertOk()
                 ->assertJson(['success' => true])
                 ->assertJsonStructure([
                     'data' => [
                         'assignments' => [
                             '*' => [
                                 'id',
                                 'user' => ['id', 'name', 'email', 'state'],
                                 'role' => ['id', 'name'],
                             ]
                         ],
                         'total'
                     ]
                 ]);

        $this->assertDatabaseHas('user_role', [
            'id' => $assignment->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }

    /**
     * Test filtering assignments by User
     */
    public function test_can_filter_assignments_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $role = Role::factory()->create();

        UserRole::factory()->create([
            'user_id' => $user1->id,
            'role_id' => $role->id,
        ]);

        UserRole::factory()->create([
            'user_id' => $user2->id,
            'role_id' => $role->id,
        ]);

        $response = $this->getJson(self::BASE_URL . '?user_id=' . $user1->id, $this->headers);

        $response->assertOk()
                 ->assertJson(['success' => true])
                 ->assertJsonPath('data.total', 1);
    }

    /**
     * Test filtering assignments by Role
     */
    public function test_can_filter_assignments_by_role(): void
    {
        $user = User::factory()->create();
        $role1 = Role::factory()->create();
        $role2 = Role::factory()->create();

        UserRole::factory()->create([
            'user_id' => $user->id,
            'role_id' => $role1->id,
        ]);

        UserRole::factory()->create([
            'user_id' => $user->id,
            'role_id' => $role2->id,
        ]);

        $response = $this->getJson(self::BASE_URL . '?role_id=' . $role1->id, $this->headers);

        $response->assertOk()
                 ->assertJson(['success' => true])
                 ->assertJsonPath('data.total', 1);
    }

    /**
     * Test creating a new assignment
     */
    public function test_can_create_assignment(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ], $this->headers);

        $response->assertCreated()
                 ->assertJson(['success' => true])
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'user' => ['id', 'name', 'email'],
                         'role' => ['id', 'name'],
                     ]
                 ]);

        $this->assertDatabaseHas('user_role', [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }

    /**
     * Test that duplicate assignments are prevented by database constraint
     */
    public function test_cannot_create_duplicate_assignment(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        // Create first assignment
        $this->postJson(self::BASE_URL, [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ], $this->headers)->assertCreated();

        // Try to create duplicate
        $response = $this->postJson(self::BASE_URL, [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ], $this->headers);

        $response->assertStatus(400)
                 ->assertJson(['success' => false])
                 ->assertJsonFragment(['message' => 'This user already has this role assigned']);
    }

    /**
     * Test validation for required fields
     */
    public function test_create_assignment_requires_user_id(): void
    {
        $role = Role::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'role_id' => $role->id,
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJson(['success' => false])
                 ->assertJsonValidationErrors(['user_id']);
    }

    /**
     * Test validation for required fields
     */
    public function test_create_assignment_requires_role_id(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'user_id' => $user->id,
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJson(['success' => false])
                 ->assertJsonValidationErrors(['role_id']);
    }

    /**
     * Test validation for existing User
     */
    public function test_create_assignment_validates_user_exists(): void
    {
        $role = Role::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'user_id' => 99999, // Non-existent ID
            'role_id' => $role->id,
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJson(['success' => false])
                 ->assertJsonValidationErrors(['user_id']);
    }

    /**
     * Test validation for existing Role
     */
    public function test_create_assignment_validates_role_exists(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'user_id' => $user->id,
            'role_id' => 99999, // Non-existent ID
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJson(['success' => false])
                 ->assertJsonValidationErrors(['role_id']);
    }

    /**
     * Test retrieving a specific assignment
     */
    public function test_can_get_assignment_by_id(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $assignment = UserRole::factory()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);

        $response = $this->getJson(self::BASE_URL . '/' . $assignment->id, $this->headers);

        $response->assertOk()
                 ->assertJson(['success' => true])
                 ->assertJsonPath('data.id', $assignment->id)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'user' => ['id', 'name', 'email', 'state'],
                         'role' => ['id', 'name'],
                     ]
                 ]);
    }

    /**
     * Test retrieving non-existent assignment returns 404
     */
    public function test_get_nonexistent_assignment_returns_404(): void
    {
        $response = $this->getJson(self::BASE_URL . '/99999', $this->headers);

        $response->assertNotFound()
                 ->assertJson(['success' => false]);
    }

    /**
     * Test updating an assignment
     */
    public function test_can_update_assignment(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $role1 = Role::factory()->create();
        $role2 = Role::factory()->create();

        $assignment = UserRole::factory()->create([
            'user_id' => $user1->id,
            'role_id' => $role1->id,
        ]);

        $response = $this->putJson(self::BASE_URL . '/' . $assignment->id, [
            'user_id' => $user2->id,
            'role_id' => $role2->id,
        ], $this->headers);

        $response->assertOk()
                 ->assertJson(['success' => true])
                 ->assertJsonPath('data.id', $assignment->id);

        $this->assertDatabaseHas('user_role', [
            'id' => $assignment->id,
            'user_id' => $user2->id,
            'role_id' => $role2->id,
        ]);
    }

    /**
     * Test updating non-existent assignment returns 404
     */
    public function test_update_nonexistent_assignment_returns_404(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $response = $this->putJson(self::BASE_URL . '/99999', [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ], $this->headers);

        $response->assertNotFound()
                 ->assertJson(['success' => false]);
    }

    /**
     * Test updating to duplicate combination fails
     */
    public function test_cannot_update_to_duplicate_combination(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $role = Role::factory()->create();

        // Create first assignment
        UserRole::factory()->create([
            'user_id' => $user1->id,
            'role_id' => $role->id,
        ]);

        // Create second assignment
        $assignment2 = UserRole::factory()->create([
            'user_id' => $user2->id,
            'role_id' => $role->id,
        ]);

        // Try to update second to match first
        $response = $this->putJson(self::BASE_URL . '/' . $assignment2->id, [
            'user_id' => $user1->id,
            'role_id' => $role->id,
        ], $this->headers);

        $response->assertStatus(400)
                 ->assertJson(['success' => false]);
    }

    /**
     * Test deleting an assignment (physical delete)
     */
    public function test_can_delete_assignment(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $assignment = UserRole::factory()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);

        $response = $this->deleteJson(self::BASE_URL . '/' . $assignment->id, [], $this->headers);

        $response->assertOk()
                 ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('user_role', [
            'id' => $assignment->id,
        ]);
    }

    /**
     * Test deleting non-existent assignment returns 404
     */
    public function test_delete_nonexistent_assignment_returns_404(): void
    {
        $response = $this->deleteJson(self::BASE_URL . '/99999', [], $this->headers);

        $response->assertNotFound()
                 ->assertJson(['success' => false]);
    }

    /**
     * Test that a user can have multiple roles
     */
    public function test_user_can_have_multiple_roles(): void
    {
        $user = User::factory()->create();
        $role1 = Role::factory()->create(['name' => 'Admin']);
        $role2 = Role::factory()->create(['name' => 'Manager']);

        $this->postJson(self::BASE_URL, [
            'user_id' => $user->id,
            'role_id' => $role1->id,
        ], $this->headers)->assertCreated();

        $this->postJson(self::BASE_URL, [
            'user_id' => $user->id,
            'role_id' => $role2->id,
        ], $this->headers)->assertCreated();

        $response = $this->getJson(self::BASE_URL . '?user_id=' . $user->id, $this->headers);

        $response->assertOk()
                 ->assertJsonPath('data.total', 2);
    }
}
