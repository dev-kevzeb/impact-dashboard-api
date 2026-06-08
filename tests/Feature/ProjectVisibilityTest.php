<?php

namespace Tests\Feature;

use App\Modules\Country\Domain\Country;
use App\Modules\CountryUserRole\Domain\CountryUserRole;
use App\Modules\Currency\Domain\Currency;
use App\Modules\Indicator\Domain\Indicator;
use App\Modules\InviteProgram\Domain\InviteProgram;
use App\Modules\Kpa\Domain\Kpa;
use App\Modules\Program\Domain\Program;
use App\Modules\ProgramCountryUserRole\Domain\ProgramCountryUserRole;
use App\Modules\ProgramState\Domain\ProgramState;
use App\Modules\CountryKpa\Domain\CountryKpa;
use App\Modules\Sdg\Domain\Sdg;
use App\Modules\StrategicOutput\Domain\StrategicOutput;
use App\Modules\Measure\Domain\Measure;
use App\Modules\Project\Domain\Project;
use App\Modules\Agency\Domain\Agency;
use App\Modules\Donor\Domain\Donor;
use App\Modules\Role\Domain\Role;
use App\Modules\User\Domain\User;
use App\Modules\UserRole\Domain\UserRole;
use App\Modules\UserState\Domain\UserState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/projects';

    private array $ownerContext;
    private array $invitedContext;
    private Program $program;
    private Indicator $indicator;
    private Agency $agency;
    private Donor $donor;

    // Prepara datos base: usuarios PM, programa owner e invitacion al programa.
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\UserStateSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\ProgramStateSeeder::class);

        $this->ownerContext = $this->makePmContext('owner-pm@test.com', 'Owner PM', 'Owner Country');
        $this->invitedContext = $this->makePmContext('invited-pm@test.com', 'Invited PM', 'Invited Country');

        $inactiveState = ProgramState::where('name', 'Inactive')->firstOrFail();
        $this->program = Program::factory()->create(['program_state_id' => $inactiveState->id]);

        $this->indicator = $this->createProgramCountryIndicator($this->ownerContext['countryUserRole']->country_id);
        $this->agency = Agency::factory()->create();
        $this->donor = Donor::factory()->create();

        ProgramCountryUserRole::create([
            'program_id' => $this->program->id,
            'country_user_role_id' => $this->ownerContext['countryUserRole']->id,
        ]);

        InviteProgram::create([
            'program_country_user_role_id' => ProgramCountryUserRole::where('program_id', $this->program->id)->firstOrFail()->id,
            'invited_user_role_id' => $this->invitedContext['userRole']->id,
        ]);
    }

    // Crea un contexto autenticado de PM con relacion user_role y country_user_role.
    private function makePmContext(string $email, string $name, string $countryName): array
    {
        $activeState = UserState::where('name', 'active')->firstOrFail();
        $role = Role::where('name', 'project-manager')->firstOrFail();

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'Password1!',
                'user_state_id' => $activeState->id,
            ]
        );

        $userRole = UserRole::firstOrCreate([
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);

        $currency = Currency::firstOrCreate(['code' => 'USD'], ['name' => 'US Dollar']);
        $country = Country::firstOrCreate(
            ['name' => $countryName],
            ['currency_id' => $currency->id, 'active' => true]
        );

        $countryUserRole = CountryUserRole::firstOrCreate([
            'country_id' => $country->id,
            'user_role_id' => $userRole->id,
        ]);

        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        $token = $guard->login($user);

        return [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
            'user' => $user,
            'userRole' => $userRole,
            'countryUserRole' => $countryUserRole,
        ];
    }

    // Construye la jerarquia country-kpa-output-measure-indicator para el pais del owner.
    private function createProgramCountryIndicator(int $countryId): Indicator
    {
        $kpa = Kpa::factory()->create();

        $countryKpa = CountryKpa::firstOrCreate([
            'id_country' => $countryId,
            'id_kpa' => $kpa->id,
        ]);

        $strategicOutput = StrategicOutput::factory()->create([
            'id_ck' => $countryKpa->id,
        ]);

        $measure = Measure::factory()->create([
            'strategic_output_id' => $strategicOutput->id,
        ]);

        return Indicator::factory()->create([
            'measure_id' => $measure->id,
        ]);
    }

    // Crea un proyecto via API con payload valido y retorna el modelo persistido.
    private function createProject(array $headers, string $name): Project
    {
        $beneficiary = \App\Modules\Beneficiary\Domain\Beneficiary::factory()->create();
        $state = \App\Modules\ProjectState\Domain\ProjectState::firstOrCreate(['state' => 'En Progreso']);

        $response = $this->postJson(self::BASE_URL, [
            'program_id' => $this->program->id,
            'name' => $name,
            'description' => 'Valid project description for visibility testing',
            'project_url' => 'https://example.com/' . strtolower(str_replace(' ', '-', $name)),
            'start_date' => '2026-01-10',
            'end_date' => '2026-02-10',
            'progress' => 0,
            'comments' => 'Initial comment',
            'budget' => 1000,
            'weight' => 0.25,
            'contact' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'title' => 'PM',
                'email' => 'john.doe@example.com',
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
                    'id' => $this->indicator->id,
                    'name' => $this->indicator->name,
                ],
            ],
            'donors' => [
                [
                    'id' => $this->donor->id,
                    'name' => $this->donor->name,
                    'contribution' => 100,
                ],
            ],
            'agencies' => [
                [
                    'id' => $this->agency->id,
                    'name' => $this->agency->name,
                    'contribution' => 100,
                ],
            ],
        ], $headers);

        $response->assertCreated();

        return Project::findOrFail($response->json('data.id'));
    }

    // Arma el payload de update reutilizando entidades ya asociadas al proyecto.
    private function buildProjectUpdatePayload(Project $project, string $name): array
    {
        $contact = \App\Modules\Contact\Domain\Contact::findOrFail((int) $project->contact_id);
        $beneficiary = \App\Modules\Beneficiary\Domain\Beneficiary::findOrFail((int) $project->beneficiary_id);
        $state = \App\Modules\ProjectState\Domain\ProjectState::findOrFail((int) $project->project_state_id);

        return [
            'program_id' => $project->program_id,
            'name' => $name,
            'description' => 'Updated project description for permissions tests',
            'project_url' => 'https://example.com/' . strtolower(str_replace(' ', '-', $name)),
            'start_date' => '2025-01-10',
            'end_date' => '2025-02-10',
            'progress' => 35,
            'comments' => 'Updated comment',
            'budget' => 1500,
            'weight' => 0.25,
            'contact' => [
                'id' => $contact->id,
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
            'indicators' => [
                [
                    'id' => $this->indicator->id,
                    'name' => $this->indicator->name,
                ],
            ],
            'donors' => [
                [
                    'id' => $this->donor->id,
                    'name' => $this->donor->name,
                    'contribution' => 100,
                ],
            ],
            'agencies' => [
                [
                    'id' => $this->agency->id,
                    'name' => $this->agency->name,
                    'contribution' => 100,
                ],
            ],
        ];
    }

    // Arma un payload valido para probar update de programa por usuario invitado.
    private function buildProgramUpdatePayload(string $name): array
    {
        $state = ProgramState::where('name', 'Inactive')->firstOrFail();
        $sdg = Sdg::query()->create([
            'image' => 'sdg-test.png',
            'filename' => 'sdg-test.png',
        ]);

        return [
            'name' => $name,
            'description' => 'Updated program description for permissions tests',
            'program_url' => 'https://example.com/updated-program',
            'program_state_id' => $state->id,
            'sdg_ids' => [$sdg->id],
            'contact' => [
                'first_name' => 'Program',
                'last_name' => 'Owner',
                'title' => 'Manager',
                'email' => 'program.owner@example.com',
                'phone' => '+59170000000',
            ],
        ];
    }

    // Verifica que el owner ve todos los proyectos del programa pero solo edita los suyos.
    public function test_owner_sees_all_program_projects_but_only_can_edit_own(): void
    {
        $ownerProject = $this->createProject($this->ownerContext['headers'], 'Owner Project');
        $invitedProject = $this->createProject($this->invitedContext['headers'], 'Invited Project');

        $response = $this->getJson(self::BASE_URL . '/program/' . $this->program->id, $this->ownerContext['headers']);

        $response->assertOk();

        $projects = collect($response->json('data.projects'));

        $this->assertCount(2, $projects);
        $this->assertTrue((bool) $projects->firstWhere('id', $ownerProject->id)['can_edit']);
        $this->assertTrue((bool) $projects->firstWhere('id', $invitedProject->id)['can_edit']);
    }

    // Verifica que el PM invitado solo visualiza proyectos creados por el mismo.
    public function test_invited_pm_only_sees_projects_they_create(): void
    {
        $this->createProject($this->ownerContext['headers'], 'Owner Project');
        $this->createProject($this->invitedContext['headers'], 'Invited Project');

        $response = $this->getJson(self::BASE_URL . '/program/' . $this->program->id, $this->invitedContext['headers']);

        $response->assertOk();

        $projects = collect($response->json('data.projects'));

        $this->assertCount(0, $projects);
    }

    // Verifica que al crear proyecto se persiste ownership en project_invite_user.
    public function test_project_creation_persists_project_invite_user_relation(): void
    {
        $project = $this->createProject($this->ownerContext['headers'], 'Owner Project');

        $this->assertDatabaseHas('project_invite_user', [
            'project_id' => $project->id,
            'country_user_role_id' => $this->ownerContext['countryUserRole']->id,
        ]);
    }

    // Verifica que el owner puede eliminar cualquier proyecto del programa.
    public function test_owner_cannot_delete_project_created_by_invited_pm(): void
    {
        $invitedProject = $this->createProject($this->invitedContext['headers'], 'Invited Project');

        $response = $this->deleteJson(self::BASE_URL . '/' . $invitedProject->id, [], $this->ownerContext['headers']);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('project', ['id' => $invitedProject->id]);
    }

    // Verifica que el owner puede actualizar proyectos creados por el invitado.
    public function test_owner_cannot_update_project_created_by_invited_pm(): void
    {
        $invitedProject = $this->createProject($this->invitedContext['headers'], 'Invited Project');
        $payload = $this->buildProjectUpdatePayload($invitedProject, 'Invited Project Updated By Owner');

        $response = $this->putJson(self::BASE_URL . '/' . $invitedProject->id, $payload, $this->ownerContext['headers']);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('project', [
            'id' => $invitedProject->id,
            'name' => 'Invited Project Updated By Owner',
        ]);
    }

    // Verifica que el invitado no puede actualizar proyectos creados por el owner.
    public function test_invited_pm_cannot_update_project_created_by_owner(): void
    {
        $ownerProject = $this->createProject($this->ownerContext['headers'], 'Owner Project');
        $payload = $this->buildProjectUpdatePayload($ownerProject, 'Owner Project Updated By Invited');

        $response = $this->putJson(self::BASE_URL . '/' . $ownerProject->id, $payload, $this->invitedContext['headers']);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'You do not have access to this project.');

        $this->assertDatabaseMissing('project', [
            'id' => $ownerProject->id,
            'name' => 'Owner Project Updated By Invited',
        ]);
    }

    // Verifica que un invitado no tiene permisos para eliminar el programa.
    public function test_invited_pm_cannot_delete_invited_program(): void
    {
        $response = $this->deleteJson('/api/v1/programs/' . $this->program->id, [], $this->invitedContext['headers']);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'You do not have permission to delete this program.');

        $this->assertDatabaseHas('program', ['id' => $this->program->id]);
    }

    // Verifica que un invitado no tiene permisos para actualizar el programa.
    public function test_invited_pm_cannot_update_invited_program(): void
    {
        $payload = $this->buildProgramUpdatePayload('Program Updated By Invited PM');

        $response = $this->putJson('/api/v1/programs/' . $this->program->id, $payload, $this->invitedContext['headers']);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'You do not have permission to edit this program.');

        $this->assertDatabaseMissing('program', [
            'id' => $this->program->id,
            'name' => 'Program Updated By Invited PM',
        ]);
    }

    public function test_owner_sees_total_projects_count_in_program_list(): void
    {
        $this->createProject($this->ownerContext['headers'], 'Owner Project');
        $this->createProject($this->invitedContext['headers'], 'Invited Project');

        $response = $this->getJson('/api/v1/programs', $this->ownerContext['headers']);

        $response->assertOk();

        $program = collect($response->json('data.programs'))->firstWhere('id', $this->program->id);

        $this->assertSame(2, (int) $program['projects_count']);
    }

    public function test_invited_pm_sees_only_their_projects_count_in_program_list(): void
    {
        $this->createProject($this->ownerContext['headers'], 'Owner Project');
        $this->createProject($this->invitedContext['headers'], 'Invited Project');

        $response = $this->getJson('/api/v1/programs', $this->invitedContext['headers']);

        $response->assertOk();

        $program = collect($response->json('data.programs'))->firstWhere('id', $this->program->id);

        $this->assertNull($program, 'The invited PM should not see the program in the list');
    }
}