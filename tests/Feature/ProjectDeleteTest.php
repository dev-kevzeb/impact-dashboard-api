<?php

namespace Tests\Feature;

use App\Modules\Agency\Domain\Agency;
use App\Modules\Contact\Domain\Contact;
use App\Modules\Donor\Domain\Donor;
use App\Modules\Indicator\Domain\Indicator;
use App\Modules\Program\Domain\Program;
use App\Modules\ProgramCountryUserRole\Domain\ProgramCountryUserRole;
use App\Modules\ProgramState\Domain\ProgramState;
use App\Modules\Project\Domain\Project;
use App\Modules\ProjectAgency\Domain\ProjectAgency;
use App\Modules\ProjectDonor\Domain\ProjectDonor;
use App\Modules\ProjectIndicator\Domain\ProjectIndicator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectDeleteTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/projects';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\ProgramStateSeeder::class);
    }

    private function createProgramWithAccess(?int $countryUserRoleId = null): Program
    {
        $activeState = ProgramState::where('name', 'Active')->firstOrFail();

        $program = Program::factory()->create([
            'program_state_id' => $activeState->id,
        ]);

        $data = ['program_id' => $program->id];
        if ($countryUserRoleId !== null) {
            $data['country_user_role_id'] = $countryUserRoleId;
        }

        ProgramCountryUserRole::factory()->create($data);

        return $program;
    }

    public function test_delete_project_removes_project_related_records_and_contact(): void
    {
        $auth = $this->authHeadersWithCountry('project-manager');
        $headers = $auth['headers'];
        $program = $this->createProgramWithAccess($auth['countryUserRole']->id);

        $project = Project::factory()->create([
            'program_id' => $program->id,
        ]);

        $donor = Donor::factory()->create();
        $agency = Agency::factory()->create();
        $indicator = Indicator::factory()->create();

        ProjectDonor::create([
            'project_id' => $project->id,
            'donor_id' => $donor->id,
            'contribution' => 40,
        ]);

        ProjectAgency::create([
            'project_id' => $project->id,
            'agency_id' => $agency->id,
            'contribution' => 60,
        ]);

        ProjectIndicator::create([
            'project_id' => $project->id,
            'indicator_id' => $indicator->id,
        ]);

        $contactId = $project->contact_id;

        $response = $this->deleteJson(self::BASE_URL . '/' . $project->id, [], $headers);

        $response->assertOk()->assertJsonPath('message', 'Project deleted successfully');

        $this->assertDatabaseMissing('project', ['id' => $project->id]);
        $this->assertDatabaseMissing('project_donor', ['project_id' => $project->id]);
        $this->assertDatabaseMissing('project_agency', ['project_id' => $project->id]);
        $this->assertDatabaseMissing('project_indicator', ['project_id' => $project->id]);
        $this->assertDatabaseMissing('contact', ['id' => $contactId]);

        $this->assertDatabaseHas('program', [
            'id' => $program->id,
            'program_state_id' => ProgramState::where('name', 'Inactive')->firstOrFail()->id,
        ]);
    }

    public function test_delete_project_keeps_contact_when_still_used_by_another_project(): void
    {
        $auth = $this->authHeadersWithCountry('project-manager');
        $headers = $auth['headers'];
        $program = $this->createProgramWithAccess($auth['countryUserRole']->id);

        $sharedContact = Contact::factory()->create();

        $projectToDelete = Project::factory()->create([
            'program_id' => $program->id,
            'contact_id' => $sharedContact->id,
        ]);

        $projectToKeep = Project::factory()->create([
            'program_id' => $program->id,
            'contact_id' => $sharedContact->id,
        ]);

        $response = $this->deleteJson(self::BASE_URL . '/' . $projectToDelete->id, [], $headers);

        $response->assertOk()->assertJsonPath('message', 'Project deleted successfully');

        $this->assertDatabaseMissing('project', ['id' => $projectToDelete->id]);
        $this->assertDatabaseHas('project', ['id' => $projectToKeep->id]);
        $this->assertDatabaseHas('contact', ['id' => $sharedContact->id]);
    }

    public function test_delete_project_returns_error_when_not_found(): void
    {
        $headers = $this->authHeaders('project-manager');

        $response = $this->deleteJson(self::BASE_URL . '/999999', [], $headers);

        $response->assertStatus(400);
    }
}
