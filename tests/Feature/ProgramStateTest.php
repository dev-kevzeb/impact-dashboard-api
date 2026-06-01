<?php

namespace Tests\Feature;

use App\Modules\Program\Domain\Program;
use App\Modules\ProgramState\Domain\ProgramState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramStateTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/program_states';

    public function test_can_delete_program_state_without_relations(): void
    {
        $state = ProgramState::factory()->create();

        $response = $this->deleteJson(self::BASE_URL . "/{$state->id}", [], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('message', 'Status deleted successfully');

        $this->assertDatabaseMissing('program_state', ['id' => $state->id]);
    }

    public function test_cannot_delete_program_state_with_program_relations(): void
    {
        $state = ProgramState::factory()->create();
        Program::factory()->create(['program_state_id' => $state->id]);

        $response = $this->deleteJson(self::BASE_URL . "/{$state->id}", [], $this->authHeaders());

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'The program status cannot be deleted because it is related to other records.');

        $this->assertDatabaseHas('program_state', ['id' => $state->id]);
    }

    public function test_delete_returns_not_found_for_non_existent_program_state(): void
    {
        $response = $this->deleteJson(self::BASE_URL . '/999999', [], $this->authHeaders());

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Status not Found');
    }
}