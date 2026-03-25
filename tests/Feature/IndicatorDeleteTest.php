<?php

namespace Tests\Feature;

use App\Modules\Indicator\Domain\Indicator;
use App\Modules\Project\Domain\Project;
use App\Modules\Program\Domain\Program;
use App\Modules\ProgramCountryUserRole\Domain\ProgramCountryUserRole;
use App\Modules\ProgramState\Domain\ProgramState;
use App\Modules\ProjectIndicator\Domain\ProjectIndicator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndicatorDeleteTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/indicators';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\ProgramStateSeeder::class);
    }

    public function test_delete_indicator_removes_indicator_successfully(): void
    {
        $headers = $this->authHeaders('admin');
        $indicator = Indicator::factory()->create();

        $response = $this->deleteJson(self::BASE_URL . '/' . $indicator->id, [], $headers);

        $response->assertOk()->assertJsonPath('message', 'Indicator deleted successfully');
        $this->assertDatabaseMissing('indicator', ['id' => $indicator->id]);
    }

    public function test_delete_indicator_fails_when_assigned_to_project(): void
    {
        $headers = $this->authHeaders('admin');

        $activeState = ProgramState::where('name', 'Active')->firstOrFail();
        $program = Program::factory()->create(['program_state_id' => $activeState->id]);
        ProgramCountryUserRole::factory()->create(['program_id' => $program->id]);

        $project = Project::factory()->create(['program_id' => $program->id]);
        $indicator = Indicator::factory()->create();

        ProjectIndicator::create([
            'project_id' => $project->id,
            'indicator_id' => $indicator->id,
        ]);

        $response = $this->deleteJson(self::BASE_URL . '/' . $indicator->id, [], $headers);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Cannot delete an indicator that is assigned to one or more projects.');

        $this->assertDatabaseHas('indicator', ['id' => $indicator->id]);
    }

    public function test_delete_indicator_returns_error_when_not_found(): void
    {
        $headers = $this->authHeaders('admin');

        $response = $this->deleteJson(self::BASE_URL . '/999999', [], $headers);

        $response->assertStatus(400);
    }
}
