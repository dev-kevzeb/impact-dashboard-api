<?php

namespace Tests\Feature;

use App\Modules\Agency\Domain\Agency;
use App\Modules\Beneficiary\Domain\Beneficiary;
use App\Modules\Contact\Domain\Contact;
use App\Modules\Country\Domain\Country;
use App\Modules\CountryKpa\Domain\CountryKpa;
use App\Modules\Donor\Domain\Donor;
use App\Modules\Indicator\Domain\Indicator;
use App\Modules\Kpa\Domain\Kpa;
use App\Modules\Measure\Domain\Measure;
use App\Modules\Program\Domain\Program;
use App\Modules\ProgramState\Domain\ProgramState;
use App\Modules\Project\Domain\Project;
use App\Modules\ProjectState\Domain\ProjectState;
use App\Modules\StrategicOutput\Domain\StrategicOutput;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProgramDetailsTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/public/programs';

    public function test_public_program_details_returns_aggregated_summary_from_projects(): void
    {
        $programState = ProgramState::factory()->create(['name' => 'Active']);
        $programContact = Contact::factory()->create([
            'first_name' => 'Ana',
            'last_name' => 'Mora',
            'title' => 'Program Director',
            'email' => 'ana.mora@example.org',
            'phone' => '+679-700-111',
        ]);

        $program = Program::factory()->create([
            'program_state_id' => $programState->id,
            'contact_id' => $programContact->id,
        ]);

        $countryA = Country::factory()->create(['name' => 'Fiji']);
        $countryB = Country::factory()->create(['name' => 'Samoa']);
        $kpaA = Kpa::factory()->create();
        $kpaB = Kpa::factory()->create();

        $countryKpaA = CountryKpa::factory()->create([
            'id_country' => $countryA->id,
            'id_kpa' => $kpaA->id,
        ]);
        $countryKpaB = CountryKpa::factory()->create([
            'id_country' => $countryB->id,
            'id_kpa' => $kpaB->id,
        ]);

        $strategicOutputA = StrategicOutput::factory()->create(['id_ck' => $countryKpaA->id]);
        $strategicOutputB = StrategicOutput::factory()->create(['id_ck' => $countryKpaB->id]);

        $measureA = Measure::factory()->create(['strategic_output_id' => $strategicOutputA->id]);
        $measureB = Measure::factory()->create(['strategic_output_id' => $strategicOutputB->id]);

        $indicatorA = Indicator::factory()->create(['measure_id' => $measureA->id]);
        $indicatorB = Indicator::factory()->create(['measure_id' => $measureB->id]);

        $beneficiaryA = Beneficiary::factory()->create(['name' => 'SME Group A']);
        $beneficiaryB = Beneficiary::factory()->create(['name' => 'SME Group B']);
        $projectState = ProjectState::factory()->create();

        $projectOne = Project::factory()->create([
            'program_id' => $program->id,
            'beneficiary_id' => $beneficiaryA->id,
            'project_state_id' => $projectState->id,
            'start_date' => '2023-01-10',
            'end_date' => '2024-06-30',
            'project_budget' => 1000,
        ]);

        $projectTwo = Project::factory()->create([
            'program_id' => $program->id,
            'beneficiary_id' => $beneficiaryB->id,
            'project_state_id' => $projectState->id,
            'start_date' => '2022-05-01',
            'end_date' => '2025-12-31',
            'project_budget' => 2500,
        ]);

        $projectOne->indicators()->attach([$indicatorA->id, $indicatorB->id]);
        $projectTwo->indicators()->attach([$indicatorA->id]);

        $donorA = Donor::factory()->create(['name' => 'Donor A']);
        $donorB = Donor::factory()->create(['name' => 'Donor B']);
        $projectOne->donors()->attach($donorA->id, ['contribution' => 50]);
        $projectOne->donors()->attach($donorB->id, ['contribution' => 25]);
        $projectTwo->donors()->attach($donorB->id, ['contribution' => 10]);

        $agencyA = Agency::factory()->create(['name' => 'Agency A', 'url' => 'https://agency-a.org']);
        $agencyB = Agency::factory()->create(['name' => 'Agency B', 'url' => 'https://agency-b.org']);
        $projectOne->agencies()->attach([$agencyA->id, $agencyB->id]);
        $projectTwo->agencies()->attach([$agencyB->id]);

        $response = $this->getJson(self::BASE_URL . "/{$program->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $program->id)
            ->assertJsonPath('data.program_summary.start_date', '2022-05-01')
            ->assertJsonPath('data.program_summary.end_date', '2025-12-31')
            ->assertJsonPath('data.program_summary.status', 'Active')
            ->assertJsonPath('data.program_summary.budget', 3500)
            ->assertJsonPath('data.program_summary.contact_person.email', 'ana.mora@example.org');

        $summary = $response->json('data.program_summary');

        $this->assertEqualsCanonicalizing(
            ['Fiji', 'Samoa'],
            array_column($summary['geographical_focus'], 'name')
        );

        $this->assertEqualsCanonicalizing(
            ['SME Group A', 'SME Group B'],
            array_column($summary['beneficiaries'], 'name')
        );

        $this->assertEqualsCanonicalizing(
            ['Donor A', 'Donor B'],
            array_column($summary['donors'], 'name')
        );

        $this->assertEqualsCanonicalizing(
            ['Agency A', 'Agency B'],
            array_column($summary['implementing_agencies'], 'name')
        );
    }

    public function test_public_program_details_returns_empty_aggregates_when_program_has_no_projects(): void
    {
        $programState = ProgramState::factory()->create(['name' => 'Inactive']);
        $programContact = Contact::factory()->create([
            'first_name' => 'Noel',
            'last_name' => 'Ratu',
            'title' => 'Coordinator',
            'email' => 'noel.ratu@example.org',
        ]);

        $program = Program::factory()->create([
            'program_state_id' => $programState->id,
            'contact_id' => $programContact->id,
        ]);

        $response = $this->getJson(self::BASE_URL . "/{$program->id}");

        $response->assertOk()
            ->assertJsonPath('data.program_summary.start_date', null)
            ->assertJsonPath('data.program_summary.end_date', null)
            ->assertJsonPath('data.program_summary.status', 'Inactive')
            ->assertJsonPath('data.program_summary.budget', 0)
            ->assertJsonPath('data.program_summary.geographical_focus', [])
            ->assertJsonPath('data.program_summary.beneficiaries', [])
            ->assertJsonPath('data.program_summary.donors', [])
            ->assertJsonPath('data.program_summary.implementing_agencies', [])
            ->assertJsonPath('data.program_summary.contact_person.email', 'noel.ratu@example.org');
    }

    public function test_public_program_details_returns_404_when_program_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/999999');

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Program not Found');
    }

    public function test_public_program_details_has_expected_program_summary_structure(): void
    {
        $programState = ProgramState::factory()->create(['name' => 'Active']);
        $program = Program::factory()->create([
            'program_state_id' => $programState->id,
            'contact_id' => Contact::factory()->create()->id,
        ]);

        $response = $this->getJson(self::BASE_URL . "/{$program->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'program_summary' => [
                        'start_date',
                        'end_date',
                        'geographical_focus',
                        'beneficiaries',
                        'status',
                        'donors',
                        'budget',
                        'implementing_agencies',
                        'contact_person' => [
                            'id',
                            'first_name',
                            'last_name',
                            'title',
                            'email',
                            'phone',
                        ],
                    ],
                ],
            ]);
    }

    public function test_public_program_details_aggregates_only_projects_belonging_to_requested_program(): void
    {
        $programState = ProgramState::factory()->create(['name' => 'Active']);

        $targetProgram = Program::factory()->create([
            'program_state_id' => $programState->id,
            'contact_id' => Contact::factory()->create()->id,
        ]);

        $otherProgram = Program::factory()->create([
            'program_state_id' => $programState->id,
            'contact_id' => Contact::factory()->create()->id,
        ]);

        $projectState = ProjectState::factory()->create();
        $beneficiary = Beneficiary::factory()->create(['name' => 'Target Beneficiary']);
        $targetProject = Project::factory()->create([
            'program_id' => $targetProgram->id,
            'beneficiary_id' => $beneficiary->id,
            'project_state_id' => $projectState->id,
            'project_budget' => 100,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $targetDonor = Donor::factory()->create(['name' => 'Target Donor']);
        $targetProject->donors()->attach($targetDonor->id, ['contribution' => 10]);

        $otherProject = Project::factory()->create([
            'program_id' => $otherProgram->id,
            'beneficiary_id' => Beneficiary::factory()->create(['name' => 'Other Beneficiary'])->id,
            'project_state_id' => $projectState->id,
            'project_budget' => 99999,
            'start_date' => '2010-01-01',
            'end_date' => '2030-12-31',
        ]);

        $otherDonor = Donor::factory()->create(['name' => 'Other Donor']);
        $otherProject->donors()->attach($otherDonor->id, ['contribution' => 99]);

        $response = $this->getJson(self::BASE_URL . "/{$targetProgram->id}");

        $response->assertOk()
            ->assertJsonPath('data.program_summary.budget', 100)
            ->assertJsonPath('data.program_summary.start_date', '2024-01-01')
            ->assertJsonPath('data.program_summary.end_date', '2024-12-31');

        $summary = $response->json('data.program_summary');

        $this->assertEquals(['Target Beneficiary'], array_column($summary['beneficiaries'], 'name'));
        $this->assertEquals(['Target Donor'], array_column($summary['donors'], 'name'));
    }

    public function test_public_program_details_ignores_indicators_without_measure_for_geographical_focus(): void
    {
        $program = Program::factory()->create([
            'program_state_id' => ProgramState::factory()->create(['name' => 'Active'])->id,
            'contact_id' => Contact::factory()->create()->id,
        ]);

        $project = Project::factory()->create([
            'program_id' => $program->id,
            'project_state_id' => ProjectState::factory()->create()->id,
            'beneficiary_id' => Beneficiary::factory()->create()->id,
        ]);

        $indicatorWithoutMeasure = Indicator::factory()->create(['measure_id' => null]);
        $project->indicators()->attach($indicatorWithoutMeasure->id);

        $response = $this->getJson(self::BASE_URL . "/{$program->id}");

        $response->assertOk()
            ->assertJsonPath('data.program_summary.geographical_focus', []);
    }
}
