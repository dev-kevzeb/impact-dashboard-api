<?php

namespace Tests\Feature;

use App\Modules\Role\Domain\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/roles';

    /**
     * Test: Can create a user role
     */
    public function test_can_create_role(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'super_admin'
        ]);

        $response->assertCreated()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'name']
            ]);

        $this->assertDatabaseHas('role', ['name' => 'super_admin']);
    }

    /**
     * Test: Cannot create user role with duplicate name
     */
    public function test_cannot_create_duplicate_role(): void
    {
        Role::factory()->create(['name' => 'admin']);

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
    public function test_can_get_all_roles(): void
    {
        Role::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test: Can get a specific user role by ID
     */
    public function test_can_get_role_by_id(): void
    {
        $role = Role::factory()->create(['name' => 'editor']);

        $response = $this->getJson(self::BASE_URL . '/' . $role->id);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $role->id,
                    'name' => 'editor'
                ]
            ]);
    }

    /**
     * Test: Returns 404 when user role not found
     */
    public function test_returns_404_when_role_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/999');

        $response->assertNotFound();
    }

    /**
     * Test: Can update a user role
     */
    public function test_can_update_role(): void
    {
        $role = Role::factory()->create(['name' => 'old_role']);

        $response = $this->putJson(self::BASE_URL . '/' . $role->id, [
            'name' => 'new_role'
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => ['name' => 'new_role']
            ]);

        $this->assertDatabaseHas('role', ['name' => 'new_role']);
        $this->assertDatabaseMissing('role', ['name' => 'old_role']);
    }

    /**
     * Test: Cannot update to duplicate name
     */
    public function test_cannot_update_to_duplicate_name(): void
    {
        Role::factory()->create(['name' => 'admin']);
        $role = Role::factory()->create(['name' => 'editor']);

        $response = $this->putJson(self::BASE_URL . '/' . $role->id, [
            'name' => 'admin'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test: Can search user roles by name
     */
    public function test_can_search_roles(): void
    {
        Role::factory()->create(['name' => 'admin']);
        Role::factory()->create(['name' => 'country_manager']);
        Role::factory()->create(['name' => 'project_manager']);

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

        $this->assertDatabaseHas('role', ['name' => 'viewer']);
    }
}
