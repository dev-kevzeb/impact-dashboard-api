<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Modules\Project\Domain\Project;
use App\Modules\Contact\Domain\Contact;
use App\Modules\Beneficiary\Domain\Beneficiary;
use App\Modules\ProjectState\Domain\ProjectState;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/projects';

    private function payload($overrides = [])
    {
        $contact = Contact::factory()->create();
        $beneficiary = Beneficiary::factory()->create();
        $state = ProjectState::factory()->create();

        return array_merge([
            'name' => 'Proyecto Base',
            'description' => 'Descripción válida del proyecto',
            'project_url' => 'https://example.com',
            'start_date' => '2024-01-10',
            'end_date' => '2024-02-10',
            'progress' => 25,
            'comments' => 'Comentarios',
            'project_budget' => 5000,

            'contact' => [
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'title' => $contact->title,
                'email' => 'prueba@gmail.com',
                'phone' => $contact->phone,
            ],

            'beneficiary' => [
                'id' => $beneficiary->id,
                'name' => $beneficiary->name,
            ],

            'project_state' => [
                'id' => $state->id,
                'state' => $state->state,
            ],

        ], $overrides);
    }


    public function test_index_returns_projects()
    {
        Project::factory()->count(3)->create();
        $response = $this->getJson(self::BASE_URL);
        $response->assertOk()->assertJsonCount(3, 'data.projects');
    }

    public function test_index_returns_empty()
    {
        $response = $this->getJson(self::BASE_URL);
        $response->assertOk()->assertJsonPath('data.total', 0);
    }

    public function test_show_project()
    {
        $project = Project::factory()->create();
        $response = $this->getJson(self::BASE_URL . "/{$project->id}");
        $response->assertOk()->assertJsonPath('data.id', $project->id);
    }

    public function test_create_project()
    {
        $beneficiary = Beneficiary::factory()->create();
        $state = ProjectState::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Proyecto Base',
            'description' => 'Descripción válida del proyecto',
            'project_url' => 'https://example.com',
            'start_date' => '2024-01-10',
            'end_date' => '2024-02-10',
            'progress' => 25,
            'comments' => 'Comentarios',
            'project_budget' => 5000,

            'contact' => [
                'first_name' => 'Andres',
                'last_name'=> 'Gutierrez',
                'title' => 'Genrente',
                'email'=> 'test@gmail.com',

            ],

            'beneficiary' => [
                'id' => $beneficiary->id,
                'name' => $beneficiary->name
            ],

            'project_state' => [
                'id' => $state->id,
                'state' => $state->state
            ],
        ]);

        $response->assertCreated()->assertJsonPath('data.name', 'Proyecto Base');
    }


    public function test_create_with_full_objects()
    {
        $contact = Contact::factory()->make();
        $beneficiary = Beneficiary::factory()->create();
        $state = ProjectState::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Proyecto Objetos',
            'description' => 'Descripción válida del proyecto',
            'project_url' => 'https://example.com',
            'start_date' => '2024-01-01',
            'end_date' => '2024-02-01',
            'progress' => 80,
            'project_budget' => 1000,
            'contact' => $contact->toArray(),
            'beneficiary' => ['id' => $beneficiary->id, 'name' => $beneficiary->name],
            'project_state' => ['id' => $state->id, 'state' => $state->state],
        ]);

        $response->assertCreated()->assertJsonPath('data.name', 'Proyecto Objetos');
    }


    public function test_cannot_create_with_invalid_dates()
    {
        $response = $this->postJson(self::BASE_URL, $this->payload([
            'end_date' => '2020-01-01'
        ]));
        $response->assertStatus(422);
    }

    public function test_cannot_create_with_invalid_url()
    {
        $response = $this->postJson(self::BASE_URL, $this->payload([
            'project_url' => 'ftp://mala-url.com'
        ]));
        $response->assertStatus(422);
    }

    public function test_cannot_create_with_negative_budget()
    {
        $response = $this->postJson(self::BASE_URL, $this->payload([
            'project_budget' => -10
        ]));
        $response->assertStatus(422);
    }

    public function test_cannot_create_with_invalid_progress()
    {
        $response = $this->postJson(self::BASE_URL, $this->payload([
            'progress' => 150
        ]));
        $response->assertStatus(422);
    }

    public function test_validation_errors_on_store()
    {
        $response = $this->postJson(self::BASE_URL, []);
        $response->assertStatus(422)->assertJsonValidationErrors([
            'name',
            'description',
            'start_date',
            'end_date',
            'progress',
            'project_budget',
            'contact.first_name',
            'beneficiary.id',
            'project_state.id',
        ]);
    }


    public function test_update_project()
    {
        $project = Project::factory()->create();

        $contact = Contact::factory()->make();
        $beneficiary = Beneficiary::factory()->create();
        $state = ProjectState::factory()->create();

        $payload = [
            'name' => 'Proyecto Actualizado',
            'description' => $project->description,
            'project_url' => $project->project_url,
            'start_date' => $project->start_date,
            'end_date' => $project->end_date,
            'progress' => $project->progress,
            'comments' => $project->comments,
            'project_budget' => $project->project_budget,

            'contact' => [
                'id' => $project->contact_id,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'title' => $contact->title,
                'email' => $contact->email,
                'phone' => $contact->phone,
            ],

            'beneficiary' => [
                'id' => $beneficiary->id,
                'name' => $beneficiary->name,
            ],

            'project_state' => [
                'id' => $state->id,
                'state' => $state->state,
            ],
        ];

        $response = $this->putJson(self::BASE_URL . "/{$project->id}", $payload);

        $response->assertOk()->assertJsonPath('data.name', 'Proyecto Actualizado');
    }


    public function test_update_invalid_dates()
    {
        $project = Project::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$project->id}", $this->payload([
            'end_date' => '2020-01-01'
        ]));

        $response->assertStatus(422);
    }

    public function test_update_invalid_beneficiary()
    {
        $project = Project::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$project->id}", $this->payload([
            'beneficiary' => [
                'id' => 99999,
                'name' => 'Algo'
            ]
        ]));

        $response->assertStatus(422);
    }

    public function test_update_invalid_state()
    {
        $project = Project::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$project->id}", $this->payload([
            'project_state' => [
                'id' => 99999,
                'state' => 'Algo'
            ]
        ]));

        $response->assertStatus(422);
    }

    public function test_search_found()
    {
        $project = Project::factory()->create([
            'name' => 'Proyecto Único ABC'
        ]);

        $response = $this->getJson(self::BASE_URL . '/search?name=ABC');
        $response->assertOk()->assertJsonPath('data.name', $project->name);
    }

    public function test_search_not_found()
    {
        $response = $this->getJson(self::BASE_URL . '/search?name=XYZABCD123');
        $response->assertStatus(404);
    }

    public function test_domain_error_name_too_short()
    {
        $payload = $this->payload(['name' => 'aa']);
        $response = $this->postJson(self::BASE_URL, $payload);
        $response->assertStatus(422);
    }

    public function test_domain_error_description_too_short()
    {
        $payload = $this->payload(['description' => 'short']);
        $response = $this->postJson(self::BASE_URL, $payload);
        $response->assertStatus(422);
    }

    public function test_domain_error_progress_out_of_range()
    {
        $payload = $this->payload(['progress' => 200]);
        $response = $this->postJson(self::BASE_URL, $payload);
        $response->assertStatus(422);
    }

    public function test_domain_error_budget_zero()
    {
        $payload = $this->payload(['project_budget' => 0]);
        $response = $this->postJson(self::BASE_URL, $payload);
        $response->assertStatus(422);
    }
}
