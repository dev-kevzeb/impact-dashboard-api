<?php

namespace Tests\Feature;

use App\Modules\Contact\Domain\Contact;
use App\Modules\Program\Domain\Program;
use App\Modules\ProgramCountryUserRole\Domain\ProgramCountryUserRole;
use App\Modules\ProgramState\Domain\ProgramState;
use App\Modules\Project\Domain\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramDeleteTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/programs';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\ProgramStateSeeder::class);
    }

    private function createProgramWithAccess(?array $overrides = [], ?int $countryUserRoleId = null): Program
    {
        $inactiveState = ProgramState::where('name', 'Inactive')->firstOrFail();

        $program = Program::factory()->create(array_merge([
            'program_state_id' => $inactiveState->id,
        ], $overrides));

        if ($countryUserRoleId !== null) {
            ProgramCountryUserRole::create([
                'program_id' => $program->id,
                'country_user_role_id' => $countryUserRoleId,
            ]);
        }

        return $program;
    }

    public function test_delete_program_removes_program_and_orphan_contact(): void
    {
        $authContext = $this->authHeadersWithCountry('project-manager');
        $headers = $authContext['headers'];
        $program = $this->createProgramWithAccess([], $authContext['countryUserRole']->id);
        $contactId = $program->contact_id;

        $response = $this->deleteJson(self::BASE_URL . '/' . $program->id, [], $headers);

        $response->assertOk()->assertJsonPath('message', 'Program deleted successfully');

        $this->assertDatabaseMissing('program', ['id' => $program->id]);
        $this->assertDatabaseMissing('contact', ['id' => $contactId]);
    }

    public function test_delete_program_keeps_contact_when_used_by_another_program(): void
    {
        $authContext = $this->authHeadersWithCountry('project-manager');
        $headers = $authContext['headers'];

        $sharedContact = Contact::factory()->create();

        $programToDelete = $this->createProgramWithAccess(['contact_id' => $sharedContact->id], $authContext['countryUserRole']->id);
        $this->createProgramWithAccess(['contact_id' => $sharedContact->id], $authContext['countryUserRole']->id);

        $response = $this->deleteJson(self::BASE_URL . '/' . $programToDelete->id, [], $headers);

        $response->assertOk()->assertJsonPath('message', 'Program deleted successfully');

        $this->assertDatabaseMissing('program', ['id' => $programToDelete->id]);
        $this->assertDatabaseHas('contact', ['id' => $sharedContact->id]);
    }

    public function test_delete_program_keeps_contact_when_used_by_project(): void
    {
        $authContext = $this->authHeadersWithCountry('project-manager');
        $headers = $authContext['headers'];

        $sharedContact = Contact::factory()->create();

        $program = $this->createProgramWithAccess(['contact_id' => $sharedContact->id], $authContext['countryUserRole']->id);

        // Another program with a project that shares the same contact
        $anotherProgram = $this->createProgramWithAccess([], $authContext['countryUserRole']->id);
        Project::factory()->create([
            'program_id' => $anotherProgram->id,
            'contact_id' => $sharedContact->id,
        ]);

        $response = $this->deleteJson(self::BASE_URL . '/' . $program->id, [], $headers);

        $response->assertOk()->assertJsonPath('message', 'Program deleted successfully');

        $this->assertDatabaseMissing('program', ['id' => $program->id]);
        $this->assertDatabaseHas('contact', ['id' => $sharedContact->id]);
    }

    public function test_delete_program_fails_when_program_has_projects(): void
    {
        $authContext = $this->authHeadersWithCountry('project-manager');
        $headers = $authContext['headers'];
        $program = $this->createProgramWithAccess([], $authContext['countryUserRole']->id);

        Project::factory()->create(['program_id' => $program->id]);

        $response = $this->deleteJson(self::BASE_URL . '/' . $program->id, [], $headers);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Cannot delete a program that still has associated projects.');

        $this->assertDatabaseHas('program', ['id' => $program->id]);
    }

    public function test_delete_program_returns_error_when_not_found(): void
    {
        $authContext = $this->authHeadersWithCountry('project-manager');
        $headers = $authContext['headers'];

        $response = $this->deleteJson(self::BASE_URL . '/999999', [], $headers);

        $response->assertStatus(400);
    }

    public function test_delete_program_forbidden_for_unrelated_user(): void
    {
        $headers = $this->authHeaders('country-manager');
        $program = $this->createProgramWithAccess();

        $response = $this->deleteJson(self::BASE_URL . '/' . $program->id, [], $headers);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Insufficient permissions. Required scope: programs:write');

        $this->assertDatabaseHas('program', ['id' => $program->id]);
    }
}
