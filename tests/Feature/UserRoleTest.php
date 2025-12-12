<?php

namespace Tests\Feature;

use App\Modules\UserRole\Domain\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/user_roles';

    /**
     * Test: Can create a user role
     */
    public function test_can_create_user_role(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'super_admin'
        ]);

        $response->assertCreated()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'name', 'created_at', 'updated_at']
            ]);

        $this->assertDatabaseHas('user_role', ['name' => 'super_admin']);
    }

    /**
     * Test: Cannot create user role with duplicate name
     */
    public function test_cannot_create_duplicate_user_role(): void
    {
        UserRole::factory()->create(['name' => 'admin']);

        $response = $this->postJson(self::BASE_URL, [
            'name' => 'admin'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test: Name must be at least 2 characters (Domain validation)
     */
    public function test_name_must_be_at_least_2_characters(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'a'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test: Name is required
     */
    public function test_name_is_required(): void
    {
        $response = $this->postJson(self::BASE_URL, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test: Can get all user roles
     */
    public function test_can_get_all_user_roles(): void
    {
        UserRole::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test: Can get a specific user role by ID
     */
    public function test_can_get_user_role_by_id(): void
    {
        $userRole = UserRole::factory()->create(['name' => 'editor']);

        $response = $this->getJson(self::BASE_URL . '/' . $userRole->id);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $userRole->id,
                    'name' => 'editor'
                ]
            ]);
    }

    /**
     * Test: Returns 404 when user role not found
     */
    public function test_returns_404_when_user_role_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/999');

        $response->assertNotFound();
    }

    /**
     * Test: Can update a user role
     */
    public function test_can_update_user_role(): void
    {
        $userRole = UserRole::factory()->create(['name' => 'old_role']);

        $response = $this->putJson(self::BASE_URL . '/' . $userRole->id, [
            'name' => 'new_role'
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => ['name' => 'new_role']
            ]);

        $this->assertDatabaseHas('user_role', ['name' => 'new_role']);
        $this->assertDatabaseMissing('user_role', ['name' => 'old_role']);
    }

    /**
     * Test: Cannot update to duplicate name
     */
    public function test_cannot_update_to_duplicate_name(): void
    {
        UserRole::factory()->create(['name' => 'admin']);
        $userRole = UserRole::factory()->create(['name' => 'editor']);

        $response = $this->putJson(self::BASE_URL . '/' . $userRole->id, [
            'name' => 'admin'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test: Can search user roles by name
     */
    public function test_can_search_user_roles(): void
    {
        UserRole::factory()->create(['name' => 'admin']);
        UserRole::factory()->create(['name' => 'country_manager']);
        UserRole::factory()->create(['name' => 'project_manager']);

        $response = $this->getJson(self::BASE_URL . '/search?q=manager');

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(2, 'data');
    }

    /**
     * Test: Search requires query parameter
     */
    public function test_search_requires_query_parameter(): void
    {
        $response = $this->getJson(self::BASE_URL . '/search');

        $response->assertStatus(422);
    }

    /**
     * Test: Name cannot exceed 50 characters
     */
    public function test_name_cannot_exceed_50_characters(): void
    {
        $longName = str_repeat('a', 51);

        $response = $this->postJson(self::BASE_URL, [
            'name' => $longName
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test: Name is trimmed before saving
     */
    public function test_name_is_trimmed_before_saving(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => '  viewer  '
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('user_role', ['name' => 'viewer']);
    }
}
