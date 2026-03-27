<?php

namespace Tests\Feature;

use App\Modules\Agency\Domain\Agency;
use App\Modules\Program\Domain\Program;
use App\Modules\Project\Domain\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgencyTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/agencies';

    public function test_can_list_agencies(): void
    {
        Agency::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL, $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'agencies' => [
                        '*' => ['id', 'name', 'url', 'is_approved']
                    ],
                    'total'
                ]
            ])
            ->assertJsonPath('data.total', 3)
            ->assertJsonCount(3, 'data.agencies');
    }

    public function test_list_returns_empty_when_no_agencies(): void
    {
        $response = $this->getJson(self::BASE_URL, $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.total', 0)
            ->assertJsonCount(0, 'data.agencies');
    }

    public function test_can_create_agency(): void
    {
        $data = [
            'name' => 'Agencia Cooperante',
            'url' => 'https://example.org',
            'is_approved' => true
        ];

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertCreated()
            ->assertJsonPath('message', 'Agency created successfully')
            ->assertJsonStructure([
                'data' => ['id', 'name', 'url', 'is_approved']
            ]);

        $this->assertDatabaseHas('agency', [
            'name' => 'Agencia Cooperante',
            'url' => 'https://example.org',
            'is_approved' => 1
        ]);
    }

    public function test_validation_errors_on_create(): void
    {
        $response = $this->postJson(self::BASE_URL, [], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'is_approved']);
    }

    public function test_can_create_agency_without_url(): void
    {
        $data = [
            'name' => 'Agencia Sin URL',
            'is_approved' => false
        ];

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertCreated()
            ->assertJsonPath('data.url', '');

        $this->assertDatabaseHas('agency', ['name' => 'Agencia Sin URL', 'url' => '']);
    }

    public function test_url_validation_error_on_create(): void
    {
        $data = [
            'name' => 'Agencia Test',
            'url' => 'invalid-url',
            'is_approved' => true
        ];

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    public function test_can_show_agency(): void
    {
        $agency = Agency::factory()->create();

        $response = $this->getJson(self::BASE_URL . "/{$agency->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Agency Found',
                'data' => [
                    'id' => $agency->id,
                    'name' => $agency->name,
                    'url' => $agency->url,
                    'is_approved' => (bool) $agency->is_approved,
                ]
            ]);
    }

    public function test_show_returns_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/9999', $this->authHeaders());

        $response->assertNotFound()
            ->assertJsonPath('message', 'Agency not Found');
    }

    public function test_can_update_agency(): void
    {
        $agency = Agency::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$agency->id}", [
            'name' => 'Agencia Actualizada',
            'url' => 'https://updated.org',
            'is_approved' => false
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('message', 'Agency updated successfully');

        $this->assertDatabaseHas('agency', [
            'id' => $agency->id,
            'name' => 'Agencia Actualizada',
            'url' => 'https://updated.org',
            'is_approved' => 0
        ]);
    }

    public function test_can_update_agency_removing_url(): void
    {
        $agency = Agency::factory()->create(['url' => 'https://original.org']);

        $response = $this->putJson(self::BASE_URL . "/{$agency->id}", [
            'name' => $agency->name,
            'is_approved' => (bool) $agency->is_approved,
        ], $this->authHeaders());

        $response->assertOk();

        $this->assertDatabaseHas('agency', [
            'id' => $agency->id,
            'url' => ''
        ]);
    }

    public function test_validation_errors_on_update(): void
    {
        $agency = Agency::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$agency->id}", [
            'name' => '',
            'is_approved' => null
        ], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'is_approved']);
    }

    public function test_url_validation_error_on_update(): void
    {
        $agency = Agency::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$agency->id}", [
            'name' => 'Valid Name',
            'url' => 'notaurl',
            'is_approved' => true
        ], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    public function test_can_search_agency_by_name(): void
    {
        Agency::factory()->create(['name' => 'Agencia Boliviana']);

        $response = $this->getJson(self::BASE_URL . '/search?name=boliviana', $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.name', 'Agencia Boliviana');
    }

    public function test_search_returns_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/search?name=xyz', $this->authHeaders());

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Agency not Found');
    }

    public function test_can_delete_agency_without_relations(): void
    {
        $agency = Agency::factory()->create();

        $response = $this->deleteJson(self::BASE_URL . "/{$agency->id}", [], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('message', 'Agency deleted successfully');

        $this->assertDatabaseMissing('agency', ['id' => $agency->id]);
    }

    public function test_cannot_delete_agency_with_project_relations(): void
    {
        $agency = Agency::factory()->create();
        $program = Program::factory()->create();
        $project = Project::factory()->create(['program_id' => $program->id]);

        DB::table('project_agency')->insert([
            'project_id' => $project->id,
            'agency_id' => $agency->id,
            'contribution' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->deleteJson(self::BASE_URL . "/{$agency->id}", [], $this->authHeaders());

        $response->assertStatus(409)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('agency', ['id' => $agency->id]);
    }

    public function test_delete_returns_not_found_for_non_existent_agency(): void
    {
        $response = $this->deleteJson(self::BASE_URL . '/999999', [], $this->authHeaders());

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Agency not Found');
    }
}
