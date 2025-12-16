<?php

namespace Tests\Feature;

use App\Modules\UserState\Domain\UserState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserStateTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/user_states';

    // ==================== CREATE TESTS ====================

    public function test_can_create_user_state(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'active'
        ]);

        $response->assertCreated()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'name', 'created_at', 'updated_at']
            ]);

        $this->assertDatabaseHas('user_state', [
            'name' => 'active'
        ]);
    }

    public function test_cannot_create_duplicate_user_state(): void
    {
        UserState::factory()->create(['name' => 'inactive']);

        $response = $this->postJson(self::BASE_URL, [
            'name' => 'inactive'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_cannot_create_user_state_with_short_name(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'a'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_cannot_create_user_state_with_long_name(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => str_repeat('a', 51) // 51 characters
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_cannot_create_user_state_without_name(): void
    {
        $response = $this->postJson(self::BASE_URL, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_trims_whitespace_when_creating_user_state(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => '  suspended  '
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('user_state', [
            'name' => 'suspended' // Trimmed
        ]);
    }

    // ==================== READ TESTS ====================

    public function test_can_list_all_user_states(): void
    {
        UserState::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'created_at', 'updated_at']
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_get_user_state_by_id(): void
    {
        $userState = UserState::factory()->create(['name' => 'active']);

        $response = $this->getJson(self::BASE_URL . '/' . $userState->id);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $userState->id,
                    'name' => 'active'
                ]
            ]);
    }

    public function test_returns_404_when_user_state_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/999999');

        $response->assertNotFound()
            ->assertJson(['success' => false]);
    }

    // ==================== UPDATE TESTS ====================

    public function test_can_update_user_state(): void
    {
        $userState = UserState::factory()->create(['name' => 'pending']);

        $response = $this->putJson(self::BASE_URL . '/' . $userState->id, [
            'name' => 'approved'
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $userState->id,
                    'name' => 'approved'
                ]
            ]);

        $this->assertDatabaseHas('user_state', [
            'id' => $userState->id,
            'name' => 'approved'
        ]);
    }

    public function test_cannot_update_to_duplicate_name(): void
    {
        $userState1 = UserState::factory()->create(['name' => 'active']);
        $userState2 = UserState::factory()->create(['name' => 'inactive']);

        $response = $this->putJson(self::BASE_URL . '/' . $userState2->id, [
            'name' => 'active' // Already exists
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    // ==================== SEARCH TESTS ====================

    public function test_can_search_user_states_with_like_pattern(): void
    {
        UserState::factory()->create(['name' => 'active']);
        UserState::factory()->create(['name' => 'inactive']);
        UserState::factory()->create(['name' => 'suspended']);

        $response = $this->getJson(self::BASE_URL . '/search?q=active');

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(2, 'data'); // Should find 'active' and 'inactive'

        $data = $response->json('data');
        $names = array_column($data, 'name');

        $this->assertContains('active', $names);
        $this->assertContains('inactive', $names);
        $this->assertNotContains('suspended', $names);
    }

    public function test_search_returns_404_when_no_matches(): void
    {
        UserState::factory()->create(['name' => 'active']);

        $response = $this->getJson(self::BASE_URL . '/search?q=xyz123');

        $response->assertNotFound()
            ->assertJson(['success' => false]);
    }

    public function test_search_requires_query_parameter(): void
    {
        $response = $this->getJson(self::BASE_URL . '/search');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
    }

    public function test_search_is_case_insensitive(): void
    {
        UserState::factory()->create(['name' => 'ACTIVE']);

        $response = $this->getJson(self::BASE_URL . '/search?q=active');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    // ==================== DOMAIN VALIDATION TESTS ====================

    public function test_domain_validates_empty_name(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(UserState::$ERROR_NAME_EMPTY);

        UserState::at('');
    }

    public function test_domain_validates_min_length(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(UserState::$ERROR_NAME_MIN_LENGTH);

        UserState::at('a');
    }

    public function test_domain_validates_max_length(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(UserState::$ERROR_NAME_MAX_LENGTH);

        UserState::at(str_repeat('a', 51));
    }

    // ==================== HELPER METHOD TESTS ====================

    public function test_has_state_method_active(): void
    {
        $activeState = UserState::factory()->create(['name' => 'active']);
        $inactiveState = UserState::factory()->create(['name' => 'inactive']);

        $this->assertTrue($activeState->hasState('active'));
        $this->assertFalse($inactiveState->hasState('active'));
    }

    public function test_has_state_method_inactive(): void
    {
        $inactiveState = UserState::factory()->create(['name' => 'inactive']);
        $activeState = UserState::factory()->create(['name' => 'active']);

        $this->assertTrue($inactiveState->hasState('inactive'));
        $this->assertFalse($activeState->hasState('inactive'));
    }

    public function test_has_state_method_suspended(): void
    {
        $suspendedState = UserState::factory()->create(['name' => 'suspended']);
        $activeState = UserState::factory()->create(['name' => 'active']);

        $this->assertTrue($suspendedState->hasState('suspended'));
        $this->assertFalse($activeState->hasState('suspended'));
    }

    public function test_has_state_is_case_insensitive(): void
    {
        $state = UserState::factory()->create(['name' => 'active']);

        $this->assertTrue($state->hasState('ACTIVE'));
        $this->assertTrue($state->hasState('Active'));
        $this->assertTrue($state->hasState('active'));
    }

    public function test_has_state_handles_whitespace(): void
    {
        $state = UserState::factory()->create(['name' => 'active']);

        $this->assertTrue($state->hasState('  active  '));
        $this->assertTrue($state->hasState('active '));
        $this->assertTrue($state->hasState(' active'));
    }
}
