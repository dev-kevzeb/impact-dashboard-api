<?php

namespace Tests\Feature;

use App\Modules\Country\Domain\Country;
use App\Modules\CountryKpa\Domain\CountryKpa;
use App\Modules\CountryKpaUser\Domain\CountryKpaUser;
use App\Modules\Kpa\Domain\Kpa;
use App\Modules\Role\Domain\Role;
use App\Modules\User\Domain\User;
use App\Modules\UserRole\Domain\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryKpaUserTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/country_kpa_users';

    /**
     * Test retrieving all assignments
     */
    public function test_can_list_all_assignments(): void
    {
        // Create test data
        $countryKpa = $this->createCountryKpa();
        $user = $this->createUserRole();
        
        $assignment = CountryKpaUser::factory()->create([
            'country_kpa_id' => $countryKpa->id,
            'user_role_id' => $user->id,
        ]);

        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
                 ->assertJson(['success' => true])
                 ->assertJsonStructure([
                     'data' => [
                         'assignments' => [
                             '*' => [
                                 'id',
                                 'country_kpa' => ['id', 'country', 'kpa'],
                                 'user_role' => [
                                     'id',
                                     'user' => ['id', 'name', 'email'],
                                     'role' => ['id', 'name']
                                 ],
                             ]
                         ],
                         'total'
                     ]
                 ]);

        $this->assertDatabaseHas('country_kpa_user', [
            'id' => $assignment->id,
            'country_kpa_id' => $countryKpa->id,
            'user_role_id' => $user->id,
        ]);
    }

    /**
     * Test filtering assignments by CountryKpa
     */
    public function test_can_filter_assignments_by_country_kpa(): void
    {
        $countryKpa1 = $this->createCountryKpa();
        $countryKpa2 = $this->createCountryKpa();
        $user = $this->createUserRole();
        
        CountryKpaUser::factory()->create([
            'country_kpa_id' => $countryKpa1->id,
            'user_role_id' => $user->id,
        ]);
        
        CountryKpaUser::factory()->create([
            'country_kpa_id' => $countryKpa2->id,
            'user_role_id' => $user->id,
        ]);

        $response = $this->getJson(self::BASE_URL . '?country_kpa_id=' . $countryKpa1->id);

        $response->assertOk()
                 ->assertJson(['success' => true])
                 ->assertJsonPath('data.total', 1);
    }

    /**
     * Test filtering assignments by User
     */
    public function test_can_filter_assignments_by_user(): void
    {
        $countryKpa = $this->createCountryKpa();
        $user1 = $this->createUserRole();
        $user2 = $this->createUserRole();
        
        CountryKpaUser::factory()->create([
            'country_kpa_id' => $countryKpa->id,
            'user_role_id' => $user1->id,
        ]);
        
        CountryKpaUser::factory()->create([
            'country_kpa_id' => $countryKpa->id,
            'user_role_id' => $user2->id,
        ]);

        $response = $this->getJson(self::BASE_URL . '?user_role_id=' . $user1->id);

        $response->assertOk()
                 ->assertJson(['success' => true])
                 ->assertJsonPath('data.total', 1);
    }

    /**
     * Test creating a new assignment
     */
    public function test_can_create_assignment(): void
    {
        $countryKpa = $this->createCountryKpa();
        $user = $this->createUserRole();

        $response = $this->postJson(self::BASE_URL, [
            'country_kpa_id' => $countryKpa->id,
            'user_role_id' => $user->id,
        ]);

        $response->assertCreated()
                 ->assertJson(['success' => true])
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'country_kpa' => ['id', 'country', 'kpa'],
                         'user_role' => ['id', 'user' => ['id', 'name', 'email'], 'role' => ['id', 'name']],
                     ]
                 ]);

        $this->assertDatabaseHas('country_kpa_user', [
            'country_kpa_id' => $countryKpa->id,
            'user_role_id' => $user->id,
        ]);
    }

    /**
     * Test that duplicate assignments are prevented by database constraint
     */
    public function test_cannot_create_duplicate_assignment(): void
    {
        $countryKpa = $this->createCountryKpa();
        $user = $this->createUserRole();

        // Create first assignment
        $this->postJson(self::BASE_URL, [
            'country_kpa_id' => $countryKpa->id,
            'user_role_id' => $user->id,
        ])->assertCreated();

        // Try to create duplicate
        $response = $this->postJson(self::BASE_URL, [
            'country_kpa_id' => $countryKpa->id,
            'user_role_id' => $user->id,
        ]);

        $response->assertStatus(400)
                 ->assertJson(['success' => false])
                 ->assertJsonFragment(['message' => 'This user role is already assigned to this CountryKpa']);
    }

    /**
     * Test validation for required fields
     */
    public function test_create_assignment_requires_country_kpa_id(): void
    {
        $user = $this->createUserRole();

        $response = $this->postJson(self::BASE_URL, [
            'user_role_id' => $user->id,
        ]);

        $response->assertStatus(422)
                 ->assertJson(['success' => false])
                 ->assertJsonValidationErrors(['country_kpa_id']);
    }

    /**
     * Test validation for required fields
     */
    public function test_create_assignment_requires_user_role_id(): void
    {
        $countryKpa = $this->createCountryKpa();

        $response = $this->postJson(self::BASE_URL, [
            'country_kpa_id' => $countryKpa->id,
        ]);

        $response->assertStatus(422)
                 ->assertJson(['success' => false])
                 ->assertJsonValidationErrors(['user_role_id']);
    }

    /**
     * Test validation for existing CountryKpa
     */
    public function test_create_assignment_validates_country_kpa_exists(): void
    {
        $user = $this->createUserRole();

        $response = $this->postJson(self::BASE_URL, [
            'country_kpa_id' => 99999, // Non-existent ID
            'user_role_id' => $user->id,
        ]);

        $response->assertStatus(422)
                 ->assertJson(['success' => false])
                 ->assertJsonValidationErrors(['country_kpa_id']);
    }

    /**
     * Test validation for existing User
     */
    public function test_create_assignment_validates_user_exists(): void
    {
        $countryKpa = $this->createCountryKpa();

        $response = $this->postJson(self::BASE_URL, [
            'country_kpa_id' => $countryKpa->id,
            'user_role_id' => 99999, // Non-existent ID
        ]);

        $response->assertStatus(422)
                 ->assertJson(['success' => false])
                 ->assertJsonValidationErrors(['user_role_id']);
    }

    /**
     * Test retrieving a specific assignment
     */
    public function test_can_get_assignment_by_id(): void
    {
        $countryKpa = $this->createCountryKpa();
        $user = $this->createUserRole();
        
        $assignment = CountryKpaUser::factory()->create([
            'country_kpa_id' => $countryKpa->id,
            'user_role_id' => $user->id,
        ]);

        $response = $this->getJson(self::BASE_URL . '/' . $assignment->id);

        $response->assertOk()
                 ->assertJson(['success' => true])
                 ->assertJsonPath('data.id', $assignment->id)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'country_kpa' => ['id', 'country', 'kpa'],
                         'user_role' => [
                             'id',
                             'user' => ['id', 'name', 'email'],
                             'role' => ['id', 'name']
                         ],
                     ]
                 ]);
    }

    /**
     * Test retrieving non-existent assignment returns 404
     */
    public function test_get_nonexistent_assignment_returns_404(): void
    {
        $response = $this->getJson(self::BASE_URL . '/99999');

        $response->assertNotFound()
                 ->assertJson(['success' => false]);
    }

    /**
     * Test updating an assignment
     */
    public function test_can_update_assignment(): void
    {
        $countryKpa1 = $this->createCountryKpa();
        $countryKpa2 = $this->createCountryKpa();
        $user1 = $this->createUserRole();
        $user2 = $this->createUserRole();
        
        $assignment = CountryKpaUser::factory()->create([
            'country_kpa_id' => $countryKpa1->id,
            'user_role_id' => $user1->id,
        ]);

        $response = $this->putJson(self::BASE_URL . '/' . $assignment->id, [
            'country_kpa_id' => $countryKpa2->id,
            'user_role_id' => $user2->id,
        ]);

        $response->assertOk()
                 ->assertJson(['success' => true])
                 ->assertJsonPath('data.id', $assignment->id);

        $this->assertDatabaseHas('country_kpa_user', [
            'id' => $assignment->id,
            'country_kpa_id' => $countryKpa2->id,
            'user_role_id' => $user2->id,
        ]);
    }

    /**
     * Test updating non-existent assignment returns 404
     */
    public function test_update_nonexistent_assignment_returns_404(): void
    {
        $countryKpa = $this->createCountryKpa();
        $user = $this->createUserRole();

        $response = $this->putJson(self::BASE_URL . '/99999', [
            'country_kpa_id' => $countryKpa->id,
            'user_role_id' => $user->id,
        ]);

        $response->assertNotFound()
                 ->assertJson(['success' => false]);
    }

    /**
     * Test updating to duplicate combination fails
     */
    public function test_cannot_update_to_duplicate_combination(): void
    {
        $countryKpa = $this->createCountryKpa();
        $user1 = $this->createUserRole();
        $user2 = $this->createUserRole();
        
        // Create first assignment
        CountryKpaUser::factory()->create([
            'country_kpa_id' => $countryKpa->id,
            'user_role_id' => $user1->id,
        ]);
        
        // Create second assignment
        $assignment2 = CountryKpaUser::factory()->create([
            'country_kpa_id' => $countryKpa->id,
            'user_role_id' => $user2->id,
        ]);

        // Try to update second to match first
        $response = $this->putJson(self::BASE_URL . '/' . $assignment2->id, [
            'country_kpa_id' => $countryKpa->id,
            'user_role_id' => $user1->id,
        ]);

        $response->assertStatus(400)
                 ->assertJson(['success' => false]);
    }

    /**
     * Test deleting an assignment (physical delete)
     */
    public function test_can_delete_assignment(): void
    {
        $countryKpa = $this->createCountryKpa();
        $user = $this->createUserRole();
        
        $assignment = CountryKpaUser::factory()->create([
            'country_kpa_id' => $countryKpa->id,
            'user_role_id' => $user->id,
        ]);

        $response = $this->deleteJson(self::BASE_URL . '/' . $assignment->id);

        $response->assertOk()
                 ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('country_kpa_user', [
            'id' => $assignment->id,
        ]);
    }

    /**
     * Test deleting non-existent assignment returns 404
     */
    public function test_delete_nonexistent_assignment_returns_404(): void
    {
        $response = $this->deleteJson(self::BASE_URL . '/99999');

        $response->assertNotFound()
                 ->assertJson(['success' => false]);
    }

    /**
     * Helper: Create a test CountryKpa
     */
    private function createCountryKpa(): CountryKpa
    {
        $country = Country::factory()->create();
        $kpa = Kpa::factory()->create();
        
        return CountryKpa::factory()->create([
            'id_country' => $country->id,
            'id_kpa' => $kpa->id,
        ]);
    }

    /**
     * Helper: Create a test UserRole with User and Role
     */
    private function createUserRole(): UserRole
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        
        return UserRole::factory()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }
}
