<?php

namespace Tests\Feature;

use App\Modules\Agency\Domain\Agency;
use App\Modules\Donor\Domain\Donor;
use App\Modules\Indicator\Domain\Indicator;
use App\Modules\Program\Domain\Program;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Modules\Project\Domain\Project;
use App\Modules\Contact\Domain\Contact;
use App\Modules\Beneficiary\Domain\Beneficiary;
use App\Modules\ProjectState\Domain\ProjectState;
use App\Modules\ProgramCountryUserRole\Domain\ProgramCountryUserRole;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/projects';

    private array $headers = [];
    private $countryUserRole;

    protected function setUp(): void
    {
        parent::setUp();
        $auth = $this->authHeadersWithCountry('project-manager');
        $this->headers = $auth['headers'];
        $this->countryUserRole = $auth['countryUserRole'];
    }

    private function payload($overrides = [])
    {
        $contact = Contact::factory()->create();
        $beneficiary = Beneficiary::factory()->create();
        $state = ProjectState::factory()->create();

        $indicator = Indicator::factory()->create();
        $agency = Agency::factory()->create();
        $donor = Donor::factory()->create();
        $program = Program::factory()->create();

        // Link program to test user's country role so the user has access
        ProgramCountryUserRole::factory()->create([
            'program_id' => $program->id,
            'country_user_role_id' => $this->countryUserRole->id,
        ]);

        return array_merge([
            'program_id' => $program->id,
            'name' => 'Proyecto Base',
            'description' => 'Descripción válida del proyecto',
            'project_url' => 'https://example.com',
            'start_date' => '2024-01-10',
            'end_date' => '2024-02-10',
            'progress' => 25,
            'comments' => 'Comentarios',
            'budget' => 5000,
            'weight' => 0.5,

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

            'indicators' => [
                [
                    'id'=> $indicator->id,
                    'name' => $indicator->name,
                ]
            ],

            'agencies' => [
                [
                    'id' => $agency->id,
                    'name' => $agency->name,
                    'contribution' => 60.00
                ]
            ],

            'donors' => [
                [
                    'id' => $donor->id,
                    'name' => $donor->name,
                    'contribution' => 40.00,
                ]
            ]


        ], $overrides);
    }


    public function test_index_returns_projects()
    {
        Project::factory()->count(3)->create();
        $response = $this->getJson(self::BASE_URL, $this->headers);
        $response->assertOk()->assertJsonCount(3, 'data.projects');
    }

    public function test_index_returns_empty()
    {
        $response = $this->getJson(self::BASE_URL, $this->headers);
        $response->assertOk()->assertJsonPath('data.total', 0);
    }

    public function test_show_project()
    {
        $project = Project::factory()->create();
        ProgramCountryUserRole::factory()->create([
            'program_id' => $project->program_id,
            'country_user_role_id' => $this->countryUserRole->id,
        ]);

        $response = $this->getJson(self::BASE_URL . "/{$project->id}", $this->headers);
        $response->assertOk()->assertJsonPath('data.id', $project->id);
    }

    public function test_create_project()
    {
        $beneficiary = Beneficiary::factory()->create();
        $state = ProjectState::factory()->create();
        // Create indicator tied to the test country/program context
        $countryId = $this->countryUserRole->country_id;
        $countryKpa = \App\Modules\CountryKpa\Domain\CountryKpa::factory()->create(['id_country' => $countryId]);
        $strategicOutput = \App\Modules\StrategicOutput\Domain\StrategicOutput::factory()->create(['id_ck' => $countryKpa->id]);
        $measure = \App\Modules\Measure\Domain\Measure::factory()->create(['strategic_output_id' => $strategicOutput->id]);
        $indicator = Indicator::factory()->create(['measure_id' => $measure->id]);
        $agency = Agency::factory()->create();
        $donor = Donor::factory()->create();
        $program = Program::factory()->create();

        ProgramCountryUserRole::factory()->create([
            'program_id' => $program->id,
            'country_user_role_id' => $this->countryUserRole->id,
        ]);

        $response = $this->postJson(self::BASE_URL, [
            'program_id' => $program->id,
            'name' => 'Proyecto Base',
            'description' => 'Descripción válida del proyecto',
            'project_url' => 'https://example.com',
            'start_date' => '2024-01-10',
            'end_date' => '2024-02-10',
            'progress' => 25,
            'comments' => 'Comentarios',
            'budget' => 5000,
            'weight' => 0.5,

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

            'indicators' => [
                [
                    'id'=> $indicator->id,
                    'name' => $indicator->name,
                ]
            ],

            'agencies' => [
                [
                    'id' => $agency->id,
                    'name' => $agency->name,
                    'contribution' => 60.00
                ]
            ],

            'donors' => [
                [
                    'id' => $donor->id,
                    'name' => $donor->name,
                    'contribution' => 40.00,
                ]
            ]
        ], $this->headers);

        $response->assertCreated()->assertJsonPath('data.name', 'Proyecto Base');
    }

    public function test_create_with_the_same_name(){
        $program = Program::factory()->create();
        ProgramCountryUserRole::factory()->create([
            'program_id' => $program->id,
            'country_user_role_id' => $this->countryUserRole->id,
        ]);
        $project = Project::factory()->create(['name' => 'Agua Potable', 'program_id' => $program->id]);

        // Create indicator linked to program country context
        $countryId = $this->countryUserRole->country_id;
        $countryKpa = \App\Modules\CountryKpa\Domain\CountryKpa::factory()->create(['id_country' => $countryId]);
        $strategicOutput = \App\Modules\StrategicOutput\Domain\StrategicOutput::factory()->create(['id_ck' => $countryKpa->id]);
        $measure = \App\Modules\Measure\Domain\Measure::factory()->create(['strategic_output_id' => $strategicOutput->id]);
        $indicator = Indicator::factory()->create(['measure_id' => $measure->id]);
        $agency = Agency::factory()->create();
        $donor = Donor::factory()->create();

        $payload = [
            'program_id' => $program->id,
            'name' => 'Agua    potable       ',
            'description' => 'Descripción válida del proyecto',
            'project_url' => 'https://example.com',
            'start_date' => '2024-01-10',
            'end_date' => '2024-02-10',
            'progress' => 25,
            'comments' => 'Comentarios',
            'budget' => 5000,
            'weight' => 0.5,
            'contact' => [
                'first_name' => 'Andres',
                'last_name'=> 'Gutierrez',
                'title' => 'Genrente',
                'email'=> 'test@gmail.com',
            ],
            'beneficiary' => [
                'id' => $this->payload()['beneficiary']['id'] ?? 1,
                'name' => 'Benef'
            ],
            'project_state' => [
                'id' => 1,
                'state' => 'Estado'
            ],
            'indicators' => [ [ 'id' => $indicator->id, 'name' => $indicator->name ] ],
            'agencies' => [ [ 'id' => $agency->id, 'name' => $agency->name, 'contribution' => 60.0 ] ],
            'donors' => [ [ 'id' => $donor->id, 'name' => $donor->name, 'contribution' => 40.0 ] ],
        ];

        $response = $this->postJson(self::BASE_URL, $payload, $this->headers);

        $response->assertStatus(400)->assertJsonPath('message', 'The project with name Agua Potable already exist in the selected program.');
    }


    public function test_cannot_create_with_invalid_dates()
    {
        $response = $this->postJson(self::BASE_URL, $this->payload([
            'end_date' => '2020-01-01'
        ]), $this->headers);
        $response->assertStatus(422);
    }

    public function test_cannot_create_with_invalid_url()
    {
        $response = $this->postJson(self::BASE_URL, $this->payload([
            'project_url' => 'ftp://mala-url.com'
        ]), $this->headers);
        $response->assertStatus(422);
    }

    public function test_cannot_create_with_negative_budget()
    {
        $response = $this->postJson(self::BASE_URL, $this->payload([
            'budget' => -10
        ]), $this->headers);
        $response->assertStatus(422);
    }

    public function test_cannot_create_with_invalid_progress()
    {
        $response = $this->postJson(self::BASE_URL, $this->payload([
            'progress' => 150
        ]), $this->headers);
        $response->assertStatus(422);
    }


    public function test_update_project()
    {
        $project = Project::factory()->create();
        ProgramCountryUserRole::factory()->create([
            'program_id' => $project->program_id,
            'country_user_role_id' => $this->countryUserRole->id,
        ]);

        $contact = Contact::factory()->make();
        $beneficiary = Beneficiary::factory()->create();
        $state = ProjectState::factory()->create();

        $indicator = Indicator::factory()->create();
        $agency = Agency::factory()->create();
        $donor = Donor::factory()->create();

        $payload = [
            'name' => 'Proyecto Actualizado',
            'description' => $project->description,
            'project_url' => $project->project_url,
            'start_date' => $project->start_date,
            'end_date' => $project->end_date,
            'progress' => $project->progress,
            'comments' => $project->comments,
            'budget' => $project->project_budget,
            'weight' => 0.5,
            'program_id' => $project->program_id,

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
            'indicators' => [ ['id' => $indicator->id, 'name' => $indicator->name] ],
            'agencies' => [ ['id' => $agency->id, 'name' => $agency->name, 'contribution' => 50.0] ],
            'donors' => [ ['id' => $donor->id, 'name' => $donor->name, 'contribution' => 50.0] ],
        ];

        $response = $this->putJson(self::BASE_URL . "/{$project->id}", $payload, $this->headers);

        $response->assertOk()->assertJsonPath('data.name', 'Proyecto Actualizado');
    }


    public function test_update_invalid_dates()
    {
        $project = Project::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$project->id}", $this->payload([
            'end_date' => '2020-01-01'
        ]), $this->headers);

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
        ]), $this->headers);

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
        ]), $this->headers);

        $response->assertStatus(422);
    }

    public function test_search_found()
    {
        $project = Project::factory()->create([
            'name' => 'Proyecto Único ABC'
        ]);
        ProgramCountryUserRole::factory()->create([
            'program_id' => $project->program_id,
            'country_user_role_id' => $this->countryUserRole->id,
        ]);

        $response = $this->getJson(self::BASE_URL . '/search?name=ABC', $this->headers);
        $response->assertOk()->assertJsonPath('data.name', $project->name);
    }

    public function test_search_not_found()
    {
        $response = $this->getJson(self::BASE_URL . '/search?name=XYZABCD123', $this->headers);
        $response->assertStatus(404);
    }

    public function test_domain_error_name_too_short()
    {
        $payload = $this->payload(['name' => 'aa']);
        $response = $this->postJson(self::BASE_URL, $payload, $this->headers);
        $response->assertStatus(422);
    }

    public function test_domain_error_description_too_short()
    {
        $payload = $this->payload(['description' => 'short']);
        $response = $this->postJson(self::BASE_URL, $payload, $this->headers);
        $response->assertStatus(422);
    }

    public function test_domain_error_progress_out_of_range()
    {
        $payload = $this->payload(['progress' => 200]);
        $response = $this->postJson(self::BASE_URL, $payload, $this->headers);
        $response->assertStatus(422);
    }

    public function test_domain_error_budget_zero()
    {
        $payload = $this->payload(['budget' => 0]);
        $response = $this->postJson(self::BASE_URL, $payload, $this->headers);
        $response->assertStatus(400);
    }
}
