<?php

namespace Tests\Feature;

use App\Modules\Agency\Domain\Agency;
use App\Modules\Beneficiary\Domain\Beneficiary;
use App\Modules\Donor\Domain\Donor;
use App\Modules\Indicator\Domain\Indicator;
use App\Modules\Program\Domain\Program;
use App\Modules\ProgramCountryUserRole\Domain\ProgramCountryUserRole;
use App\Modules\Project\Domain\Project;
use App\Modules\ProjectState\Domain\ProjectState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectWeightValidationTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/projects';

    private function prepareProgramWithAccess(): array
    {
        $headers = $this->authHeaders('admin');
        $program = Program::factory()->create();

        ProgramCountryUserRole::factory()->create([
            'program_id' => $program->id,
        ]);

        return [$headers, $program];
    }

    private function buildPayload(int $programId, float $weight, array $overrides = []): array
    {
        $beneficiary = Beneficiary::factory()->create();
        $state = ProjectState::factory()->create([
            'state' => 'state-' . uniqid(),
        ]);
        $indicator = Indicator::factory()->create();
        $agency = Agency::factory()->create();
        $donor = Donor::factory()->create();

        $payload = [
            'program_id' => $programId,
            'name' => 'Proyecto con peso',
            'description' => 'Descripción válida del proyecto con peso.',
            'project_url' => 'https://example.com/proyecto-peso',
            'start_date' => '2024-01-10',
            'end_date' => '2024-02-10',
            'progress' => 25,
            'comments' => 'Comentarios',
            'budget' => 5000,
            'weight' => $weight,
            'contact' => [
                'first_name' => 'Ana',
                'last_name' => 'Perez',
                'title' => 'Manager',
                'email' => 'ana@example.com',
                'phone' => '123456789',
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
                    'id' => $indicator->id,
                    'name' => $indicator->name,
                ],
            ],
            'agencies' => [
                [
                    'id' => $agency->id,
                    'name' => $agency->name,
                    'contribution' => 60,
                ],
            ],
            'donors' => [
                [
                    'id' => $donor->id,
                    'name' => $donor->name,
                    'contribution' => 40,
                ],
            ],
        ];

        return array_replace_recursive($payload, $overrides);
    }

    public function test_create_project_rejects_weight_greater_than_one(): void
    {
        [$headers, $program] = $this->prepareProgramWithAccess();
        $payload = $this->buildPayload($program->id, 1.2);

        $response = $this->postJson(self::BASE_URL, $payload, $headers);

        $response
            ->assertStatus(422)
            ->assertJsonPath('errors.weight.0', 'The project weight must be between 0 and 1.');
    }

    public function test_create_project_rejects_when_program_weight_sum_exceeds_one(): void
    {
        [$headers, $program] = $this->prepareProgramWithAccess();

        Project::factory()->create([
            'program_id' => $program->id,
            'weight' => 0.80,
        ]);

        $payload = $this->buildPayload($program->id, 0.30, [
            'name' => 'Segundo proyecto',
        ]);

        $response = $this->postJson(self::BASE_URL, $payload, $headers);

        $response
            ->assertStatus(422)
            ->assertJsonPath('errors.weight.0', 'The sum of project weights for this program cannot exceed 1.');
    }

    public function test_update_project_rejects_when_program_weight_sum_exceeds_one(): void
    {
        [$headers, $program] = $this->prepareProgramWithAccess();

        Project::factory()->create([
            'program_id' => $program->id,
            'weight' => 0.60,
        ]);

        $projectB = Project::factory()->create([
            'program_id' => $program->id,
            'weight' => 0.30,
        ]);

        $payload = $this->buildPayload($program->id, 0.50, [
            'name' => 'Proyecto B actualizado',
            'contact' => [
                'id' => $projectB->contact_id,
                'first_name' => $projectB->contact->first_name,
                'last_name' => $projectB->contact->last_name,
                'title' => $projectB->contact->title,
                'email' => $projectB->contact->email,
                'phone' => $projectB->contact->phone,
            ],
        ]);

        $response = $this->putJson(self::BASE_URL . '/' . $projectB->id, $payload, $headers);

        $response
            ->assertStatus(422)
            ->assertJsonPath('errors.weight.0', 'The sum of project weights for this program cannot exceed 1.');
    }
}
