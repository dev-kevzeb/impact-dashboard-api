<?php

namespace Tests\Feature;

use App\Modules\User\Domain\User;
use App\Modules\Role\Domain\Role;
use App\Modules\UserState\Domain\UserState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/users';
    private Role $role;
    private UserState $userState;

    protected function setUp(): void
    {
        parent::setUp();
        $this->role = Role::factory()->create(['name' => 'Admin']);
        $this->userState = UserState::factory()->create(['name' => 'Activo']);
    }

    public function test_can_create_user(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'secret123',
            'role_id' => $this->role->id,
            'user_state_id' => $this->userState->id
        ]);

        $response->assertCreated()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'roles', 'userState', 'created_at', 'updated_at']
            ]);

        $this->assertDatabaseHas('user', [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        // Verify password is hashed
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotEquals('secret123', $user->password);
        $this->assertTrue(password_verify('secret123', $user->password));
    }

    public function test_can_list_all_users(): void
    {
        User::factory()->count(3)->create([
            'user_state_id' => $this->userState->id
        ]);

        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
            ->assertJson(['success' => true])
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
            ->assertJsonCount(3, 'data.users');
    }

    public function test_can_paginate_users(): void
    {
        // Crear 15 usuarios
        User::factory()->count(15)->create([
            'user_state_id' => $this->userState->id
        ]);

        // Solicitar página 1 con 5 usuarios por página
        $response = $this->getJson(self::BASE_URL . '?per_page=5');

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

        // Solicitar página 2
        $response2 = $this->getJson(self::BASE_URL . '?per_page=5&page=2');

        $response2->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'total' => 15,
                    'current_page' => 2
                ]
            ])
            ->assertJsonCount(5, 'data.users');
    }

    public function test_can_get_user_by_id(): void
    {
        $user = User::factory()->create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'user_state_id' => $this->userState->id
        ]);

        $response = $this->getJson(self::BASE_URL . '/' . $user->id);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Juan Pérez',
                    'email' => 'juan@example.com'
                ]
            ]);
    }

    public function test_can_update_user(): void
    {
        $user = User::factory()->create([
            'user_state_id' => $this->userState->id
        ]);

        $response = $this->putJson(self::BASE_URL . '/' . $user->id, [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role_id' => $this->role->id,
            'user_state_id' => $this->userState->id
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com'
                ]
            ]);

        $this->assertDatabaseHas('user', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    public function test_can_update_user_with_new_password(): void
    {
        $user = User::factory()->create([
            'user_state_id' => $this->userState->id
        ]);

        $response = $this->putJson(self::BASE_URL . '/' . $user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newpassword456',
            'role_id' => $this->role->id,
            'user_state_id' => $this->userState->id
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $user->refresh();
        $this->assertTrue(password_verify('newpassword456', $user->password));
    }

    public function test_can_search_user_by_name(): void
    {
        User::factory()->create([
            'name' => 'Maria Garcia',
            'user_state_id' => $this->userState->id
        ]);

        $response = $this->getJson(self::BASE_URL . '/search?name=Maria Garcia');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => ['name' => 'Maria Garcia']
            ]);
    }

    public function test_returns_404_when_searching_nonexistent_user(): void
    {
        $response = $this->getJson(self::BASE_URL . '/search?name=Nonexistent');

        $response->assertNotFound()
            ->assertJson(['success' => false]);
    }

    public function test_returns_404_when_getting_nonexistent_user(): void
    {
        $response = $this->getJson(self::BASE_URL . '/999');

        $response->assertNotFound()
            ->assertJson(['success' => false]);
    }

    public function test_returns_404_when_updating_nonexistent_user(): void
    {
        $response = $this->putJson(self::BASE_URL . '/999', [
            'name' => 'Test',
            'email' => 'test@example.com',
            'role_id' => $this->role->id,
            'user_state_id' => $this->userState->id
        ]);

        $response->assertNotFound()
            ->assertJson(['success' => false]);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->postJson(self::BASE_URL, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'role_id', 'user_state_id']);
    }

    public function test_validates_email_format(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'secret123',
            'role_id' => $this->role->id,
            'user_state_id' => $this->userState->id
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_validates_unique_email(): void
    {
        User::factory()->create([
            'email' => 'duplicate@example.com',
            'user_state_id' => $this->userState->id
        ]);

        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Another User',
            'email' => 'duplicate@example.com',
            'password' => 'secret123',
            'role_id' => $this->role->id,
            'user_state_id' => $this->userState->id
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_can_update_with_same_email(): void
    {
        $user = User::factory()->create([
            'email' => 'same@example.com',
            'user_state_id' => $this->userState->id
        ]);

        $response = $this->putJson(self::BASE_URL . '/' . $user->id, [
            'name' => 'Updated Name',
            'email' => 'same@example.com', // Same email
            'role_id' => $this->role->id,
            'user_state_id' => $this->userState->id
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_validates_password_min_length(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123', // Too short
            'role_id' => $this->role->id,
            'user_state_id' => $this->userState->id
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_validates_role_exists(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'secret123',
            'role_id' => 9999, // Non-existent
            'user_state_id' => $this->userState->id
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role_id']);
    }

    public function test_validates_user_state_exists(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'secret123',
            'role_id' => $this->role->id,
            'user_state_id' => 9999 // Non-existent
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_state_id']);
    }

    public function test_returns_400_for_domain_validation_error(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'T', // Too short for domain validation
            'email' => 'test@example.com',
            'password' => 'secret123',
            'role_id' => $this->role->id,
            'user_state_id' => $this->userState->id
        ]);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_trims_input_values(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => '  Test User  ',
            'email' => '  test@example.com  ',
            'password' => 'secret123',
            'role_id' => $this->role->id,
            'user_state_id' => $this->userState->id
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('user', [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
    }

    public function test_email_is_stored_lowercase(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Test User',
            'email' => 'TEST@EXAMPLE.COM',
            'password' => 'secret123',
            'role_id' => $this->role->id,
            'user_state_id' => $this->userState->id
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('user', [
            'email' => 'test@example.com'
        ]);
    }

    public function test_user_resource_does_not_expose_password(): void
    {
        $user = User::factory()->create([
            'user_state_id' => $this->userState->id
        ]);

        $response = $this->getJson(self::BASE_URL . '/' . $user->id);

        $response->assertOk()
            ->assertJsonMissing(['password']);
    }

    public function test_user_loads_relationships(): void
    {
        $user = User::factory()->create([
            'user_state_id' => $this->userState->id
        ]);

        $response = $this->getJson(self::BASE_URL . '/' . $user->id);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'roles' => [
                        '*' => ['id', 'name']
                    ],
                    'userState' => ['id', 'name']
                ]
            ]);
    }
}
