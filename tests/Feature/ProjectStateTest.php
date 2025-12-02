<?php

namespace Tests\Feature;

use App\Modules\ProjectState\Domain\ProjectState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectStateTest extends TestCase
{
    use RefreshDatabase;

    private const ERROR_MIN_LENGTH = 'El estado del proyecto debe tener al menos 3 caracteres.';

    private const BASE_URL = '/api/v1/project_states';

    public function test_can_list_project_states(): void
    {
        ProjectState::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
                 ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'project_states',
                        'total'
                    ]
                 ])
                 ->assertJsonPath('data.total', 3);
    }

    public function test_index_returns_empty_list(): void
    {
        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
                 ->assertJsonPath('data.project_states', [])
                 ->assertJsonPath('data.total', 0);
    }

    public function test_index_returns_project_states_list(): void
    {
        ProjectState::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
                 ->assertJsonCount(3, 'data.project_states')
                 ->assertJsonPath('data.total', 3);
    }

    public function test_can_show_project_state(): void
    {
        $state = ProjectState::factory()->create();

        $response = $this->getJson(self::BASE_URL . "/{$state->id}");

        $response->assertOk()
                 ->assertJsonPath('data.id', $state->id)
                 ->assertJsonPath('data.state', $state->state);
    }

    public function test_show_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . "/999");

        $response->assertNotFound();
    }

    public function test_can_create_project_state(): void
    {
        $payload = [
            'state' => 'Implementación'
        ];

        $response = $this->postJson(self::BASE_URL, $payload);

        $response->assertCreated()
                 ->assertJsonPath('data.state', strtolower($payload['state']));

        $this->assertDatabaseHas('project_state', [
            'state' => strtolower($payload['state'])
        ]);
    }

    public function test_validation_errors_on_create(): void
    {
        $payload = ['state' => '']; // vacío

        $response = $this->postJson(self::BASE_URL, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['state']);
    }

    public function test_domain_errors_on_create(): void
    {
        $payload = [
            'state' => 'ab'
        ];

        $response = $this->postJson(self::BASE_URL, $payload);

         $response->assertStatus(422)
                 ->assertJsonValidationErrors(['state'])
                 ->assertJsonPath('errors.state.0', self::ERROR_MIN_LENGTH);
    }

    public function test_can_update_project_state(): void
    {
        $state = ProjectState::factory()->create([
            'state' => 'planificacion'
        ]);

        $payload = [
            'state' => 'Ejecución'
        ];

        $response = $this->putJson(self::BASE_URL . "/{$state->id}", $payload);

        $response->assertOk()
                 ->assertJsonPath('data.state', $payload['state']);

        $this->assertDatabaseHas('project_state', [
            'id' => $state->id,
            'state' => $payload['state']
        ]);
    }

    public function test_update_not_found(): void
    {
        $payload = ['state' => 'Seguimiento'];

        $response = $this->putJson(self::BASE_URL . "/999", $payload);

        $response->assertStatus(400); // por lógica del controller
    }

    public function test_validation_errors_on_update(): void
    {
        $state = ProjectState::factory()->create();

        $payload = ['state' => ''];

        $response = $this->putJson(self::BASE_URL . "/{$state->id}", $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['state']);
    }

    public function test_domain_errors_on_update(): void
    {
        $state = ProjectState::factory()->create();

        $payload = [
            'state' => 'ab'
        ];

        $response = $this->putJson(self::BASE_URL . "/{$state->id}", $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['state'])
                 ->assertJsonPath('errors.state.0', self::ERROR_MIN_LENGTH);
    }
}
