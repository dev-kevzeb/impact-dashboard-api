<?php

namespace Tests\Feature;

use App\Modules\Country\Domain\Country;
use App\Modules\Role\Domain\Role;
use App\Modules\Auth\Service\RecaptchaService;
use App\Modules\User\Domain\User;
use App\Modules\UserRole\Domain\UserRole;
use App\Modules\UserState\Domain\UserState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthRegisterWithCountryTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/auth/register';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.recaptcha.secret_key', 'test-secret');
        config()->set('services.recaptcha.expected_hostname', 'localhost');

        $this->fakeRecaptchaSuccess();

        // Seed required data
        $this->seed(\Database\Seeders\UserStateSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function fakeRecaptchaSuccess(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'hostname' => 'localhost',
            ], 200),
        ]);
    }

    private function fakeRecaptchaFailure(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => false,
                'hostname' => 'localhost',
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);
    }

    private function postRegister(array $payload)
    {
        if (!array_key_exists('g-recaptcha-response', $payload)) {
            $payload['g-recaptcha-response'] = 'test-recaptcha-token';
        }

        return $this->postJson(self::BASE_URL, $payload);
    }

    /**
     * Test: Can register user with valid country
     */
    public function test_can_register_user_with_valid_country(): void
    {
        $country = Country::factory()->create(['name' => 'Honduras']);

        $payload = [
            'name' => 'Juan Pérez',
            'email' => 'juan@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_name' => 'project-manager',
            'country_id' => $country->id,
        ];

        $response = $this->postRegister($payload);

        $response->assertCreated()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'roles',
                        'userState',
                        'country_user_role',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ])
            ->assertJsonPath('data.user.name', 'Juan Pérez')
            ->assertJsonPath('data.user.email', 'juan@test.com')
            ->assertJsonPath('data.user.country_user_role.country.id', $country->id)
            ->assertJsonPath('data.user.country_user_role.country.name', 'Honduras')
            ->assertJsonPath('data.user.roles.0.name', 'project-manager')
            ->assertJsonPath('data.user.userState.name', 'unverified');

        // Verify user was created in database
        $this->assertDatabaseHas('user', [
            'email' => 'juan@test.com',
            'name' => 'Juan Pérez',
        ]);

        // Verify user_role was created
        $user = User::where('email', 'juan@test.com')->first();
        $role = Role::where('name', 'project-manager')->first();

        $this->assertDatabaseHas('user_role', [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);

        // Verify country was assigned to user_role
        $userRole = UserRole::where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->first();

        $this->assertDatabaseHas('country_user_role', [
            'user_role_id' => $userRole->id,
            'country_id' => $country->id,
        ]);

        // Verify user can access assigned country via relationship
        $assignedCountries = $user->getAssignedCountries();
        $this->assertCount(1, $assignedCountries);
        $this->assertEquals($country->id, $assignedCountries->first()->id);
    }

    /**
     * Test: Can register country-manager with country
     */
    public function test_can_register_country_manager_with_country(): void
    {
        $country = Country::factory()->create(['name' => 'Guatemala']);

        $payload = [
            'name' => 'María López',
            'email' => 'maria@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_name' => 'country-manager',
            'country_id' => $country->id,
        ];

        $response = $this->postRegister($payload);

        $response->assertCreated()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.user.email', 'maria@test.com')
            ->assertJsonPath('data.user.roles.0.name', 'country-manager')
            ->assertJsonPath('data.user.country_user_role.country.id', $country->id);

        // Verify country assignment
        $this->assertDatabaseHas('country_user_role', [
            'country_id' => $country->id,
        ]);
    }

    /**
     * Test: Cannot register without country_id
     */
    public function test_cannot_register_without_country_id(): void
    {
        $payload = [
            'name' => 'Juan Pérez',
            'email' => 'juan@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_name' => 'project-manager',
            // country_id missing
        ];

        $response = $this->postRegister($payload);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonValidationErrors('country_id');

        // Verify user was NOT created
        $this->assertDatabaseMissing('user', [
            'email' => 'juan@test.com',
        ]);
    }

    /**
     * Test: Cannot register without reCAPTCHA token
     */
    public function test_cannot_register_without_recaptcha_token(): void
    {
        $country = Country::factory()->create();

        $payload = [
            'name' => 'Juan Pérez',
            'email' => 'juan@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_name' => 'project-manager',
            'country_id' => $country->id,
            'g-recaptcha-response' => null,
        ];

        $response = $this->postRegister($payload);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonValidationErrors('g-recaptcha-response')
            ->assertJsonPath('errors.g-recaptcha-response.0', 'reCAPTCHA validation is required.');
    }

    /**
     * Test: Cannot register with invalid reCAPTCHA token
     */
    public function test_cannot_register_with_invalid_recaptcha_token(): void
    {
        $this->mock(RecaptchaService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->andThrow(ValidationException::withMessages([
                    'g-recaptcha-response' => ['reCAPTCHA validation failed. Please try again.'],
                ]));
        });

        $country = Country::factory()->create();

        $payload = [
            'name' => 'Juan Pérez',
            'email' => 'juan@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_name' => 'project-manager',
            'country_id' => $country->id,
            'g-recaptcha-response' => 'invalid-recaptcha-token',
        ];

        $response = $this->postRegister($payload);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonValidationErrors('g-recaptcha-response')
            ->assertJsonPath('errors.g-recaptcha-response.0', 'reCAPTCHA validation failed. Please try again.');
    }

    /**
     * Test: Cannot register with invalid country_id (non-existent)
     */
    public function test_cannot_register_with_invalid_country_id(): void
    {
        $payload = [
            'name' => 'Juan Pérez',
            'email' => 'juan@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_name' => 'project-manager',
            'country_id' => 99999, // Non-existent country
        ];

        $response = $this->postRegister($payload);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonValidationErrors('country_id')
            ->assertJsonPath('errors.country_id.0', 'The selected country does not exist.');

        // Verify user was NOT created
        $this->assertDatabaseMissing('user', [
            'email' => 'juan@test.com',
        ]);
    }

    /**
     * Test: Cannot register with country_id as string
     */
    public function test_cannot_register_with_country_id_as_string(): void
    {
        $country = Country::factory()->create();

        $payload = [
            'name' => 'Juan Pérez',
            'email' => 'juan@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_name' => 'project-manager',
            'country_id' => 'invalid', // String instead of integer
        ];

        $response = $this->postRegister($payload);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonValidationErrors('country_id')
            ->assertJsonPath('errors.country_id.0', 'Invalid country selection.');
    }

    /**
     * Test: Cannot register with null country_id
     */
    public function test_cannot_register_with_null_country_id(): void
    {
        $payload = [
            'name' => 'Juan Pérez',
            'email' => 'juan@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_name' => 'project-manager',
            'country_id' => null,
        ];

        $response = $this->postRegister($payload);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonValidationErrors('country_id');
    }

    /**
     * Test: User can only have ONE country assigned
     */
    public function test_user_has_only_one_country_after_registration(): void
    {
        $country = Country::factory()->create();

        $payload = [
            'name' => 'Juan Pérez',
            'email' => 'juan@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_name' => 'project-manager',
            'country_id' => $country->id,
        ];

        $response = $this->postRegister($payload);
        $response->assertCreated();

        $user = User::where('email', 'juan@test.com')->first();
        $assignedCountries = $user->getAssignedCountries();

        // Should have exactly 1 country
        $this->assertCount(1, $assignedCountries);
        $this->assertEquals($country->id, $assignedCountries->first()->id);
    }

    /**
     * Test: Multiple users can be assigned to the same country
     */
    public function test_multiple_users_can_have_same_country(): void
    {
        $country = Country::factory()->create(['name' => 'El Salvador']);

        // Register first user
        $payload1 = [
            'name' => 'User One',
            'email' => 'user1@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_name' => 'project-manager',
            'country_id' => $country->id,
        ];

        $response1 = $this->postRegister($payload1);
        $response1->assertCreated();

        // Register second user with same country
        $payload2 = [
            'name' => 'User Two',
            'email' => 'user2@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_name' => 'country-manager',
            'country_id' => $country->id,
        ];

        $response2 = $this->postRegister($payload2);
        $response2->assertCreated();

        // Verify both users have the same country
        $user1 = User::where('email', 'user1@test.com')->first();
        $user2 = User::where('email', 'user2@test.com')->first();

        $this->assertEquals($country->id, $user1->getAssignedCountries()->first()->id);
        $this->assertEquals($country->id, $user2->getAssignedCountries()->first()->id);

        // Verify 2 records in country_user_role for this country
        $this->assertDatabaseCount('country_user_role', 2);
    }

    /**
     * Test: UserRole relationship to countries works correctly
     */
    public function test_user_role_relationship_to_countries_works(): void
    {
        $country = Country::factory()->create(['name' => 'Nicaragua']);

        $payload = [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_name' => 'project-manager',
            'country_id' => $country->id,
        ];

        $this->postRegister($payload);

        $user = User::where('email', 'test@test.com')->first();
        $userRole = $user->userRoles()->first();

        // Test UserRole->countries() relationship
        $this->assertNotNull($userRole);
        $this->assertCount(1, $userRole->countries);
        $this->assertEquals($country->id, $userRole->countries->first()->id);

        // Test hasAccessToCountry() method
        $this->assertTrue($userRole->hasAccessToCountry($country->id));
        $this->assertFalse($userRole->hasAccessToCountry(99999));
    }

    /**
     * Test: Response includes country information in correct format
     */
    public function test_response_includes_country_in_correct_format(): void
    {
        $country = Country::factory()->create([
            'name' => 'Costa Rica',
        ]);

        $payload = [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_name' => 'project-manager',
            'country_id' => $country->id,
        ];

        $response = $this->postRegister($payload);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'country_user_role' => [
                            'id',
                            'country' => ['id', 'name'],
                        ]
                    ]
                ]
            ])
            ->assertJsonPath('data.user.country_user_role.country', [
                'id' => $country->id,
                'name' => 'Costa Rica',
                'active' => false,
            ]);
    }

    /**
     * Test: Country assignment survives user state changes
     */
    public function test_country_assignment_persists_after_state_changes(): void
    {
        $country = Country::factory()->create();

        $payload = [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_name' => 'project-manager',
            'country_id' => $country->id,
        ];

        $this->postRegister($payload);

        $user = User::where('email', 'test@test.com')->first();

        // User starts as "unverified"
        $this->assertEquals('unverified', $user->userState->name);
        $assignedCountries1 = $user->getAssignedCountries();
        $this->assertCount(1, $assignedCountries1);

        // Change user state to "active"
        $activeState = UserState::where('name', 'active')->first();
        $user->user_state_id = $activeState->id;
        $user->save();

        // Reload user and verify country is still assigned
        $user->refresh();
        $assignedCountries2 = $user->getAssignedCountries();
        $this->assertCount(1, $assignedCountries2);
        $this->assertEquals($country->id, $assignedCountries2->first()->id);
    }

    /**
     * Test: Cannot register admin role with country (only project/country managers)
     */
    public function test_cannot_register_admin_role(): void
    {
        $country = Country::factory()->create();

        $payload = [
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_name' => 'admin', // Not allowed in registration
            'country_id' => $country->id,
        ];

        $response = $this->postRegister($payload);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonValidationErrors('role_name')
            ->assertJsonPath('errors.role_name.0', 'Invalid role. Only project-manager and country-manager are allowed.');
    }

    /**
     * Test: Cascade delete - when user_role is deleted, country_user_role is also deleted
     */
    public function test_cascade_delete_removes_country_assignment(): void
    {
        $country = Country::factory()->create();

        $payload = [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_name' => 'project-manager',
            'country_id' => $country->id,
        ];

        $this->postRegister($payload);

        $user = User::where('email', 'test@test.com')->first();
        $userRole = $user->userRoles()->first();

        // Verify country_user_role record exists
        $this->assertDatabaseHas('country_user_role', [
            'user_role_id' => $userRole->id,
            'country_id' => $country->id,
        ]);

        // Delete user_role
        $userRole->delete();

        // Verify country_user_role was also deleted (CASCADE)
        $this->assertDatabaseMissing('country_user_role', [
            'user_role_id' => $userRole->id,
            'country_id' => $country->id,
        ]);
    }

    /**
     * Test: Unique constraint prevents duplicate country assignments
     */
    public function test_cannot_assign_same_country_twice_to_same_user_role(): void
    {
        $country = Country::factory()->create();

        $payload = [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_name' => 'project-manager',
            'country_id' => $country->id,
        ];

        $this->postRegister($payload);

        $user = User::where('email', 'test@test.com')->first();
        $userRole = $user->userRoles()->first();

        // Try to attach the same country again manually
        $this->expectException(\Illuminate\Database\QueryException::class);
        $userRole->countries()->attach($country->id);

    }

}
