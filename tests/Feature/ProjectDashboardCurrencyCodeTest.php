<?php

namespace Tests\Feature;

use App\Modules\Agency\Domain\Agency;
use App\Modules\Beneficiary\Domain\Beneficiary;
use App\Modules\Contact\Domain\Contact;
use App\Modules\Country\Domain\Country;
use App\Modules\CountryKpa\Domain\CountryKpa;
use App\Modules\Currency\Domain\Currency;
use App\Modules\Donor\Domain\Donor;
use App\Modules\Indicator\Domain\Indicator;
use App\Modules\Measure\Domain\Measure;
use App\Modules\Program\Domain\Program;
use App\Modules\ProgramCountryUserRole\Domain\ProgramCountryUserRole;
use App\Modules\Project\Domain\Project;
use App\Modules\ProjectState\Domain\ProjectState;
use App\Modules\StrategicOutput\Domain\StrategicOutput;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectDashboardCurrencyCodeTest extends TestCase
{
    use RefreshDatabase;

    private const DASHBOARD_URL = '/api/v1/projects/dashboard';

    private array $headers = [];
    private $countryUserRole;

    protected function setUp(): void
    {
        parent::setUp();
        $auth = $this->authHeadersWithCountry('project-manager');
        $this->headers = $auth['headers'];
        $this->countryUserRole = $auth['countryUserRole'];
    }

    public function test_dashboard_row_includes_currency_code_from_project_country(): void
    {
        $country = Country::find($this->countryUserRole->country_id);
        $country->update(['active' => true]);
        $country->load('currency');
        $this->assertSame('USD', $country->currency->code);

        $project = $this->createProjectWithChain($country);

        $response = $this->getJson(self::DASHBOARD_URL . '?per_page=10', $this->headers);

        $response->assertOk()
            ->assertJsonPath('data.projects.0.id', $project->id)
            ->assertJsonPath('data.projects.0.country', $country->name)
            ->assertJsonPath('data.projects.0.currency_code', 'USD');
    }

    public function test_dashboard_rows_have_independent_currency_codes_per_country(): void
    {
        $bobCurrency = Currency::firstOrCreate(['code' => 'BOB']);
        $usdCurrency = Currency::firstOrCreate(['code' => 'USD']);

        $bolivia = Country::factory()->create([
            'name' => 'Bolivia',
            'currency_id' => $bobCurrency->id,
            'active' => true,
        ]);
        $unitedStates = Country::factory()->create([
            'name' => 'United States',
            'currency_id' => $usdCurrency->id,
            'active' => true,
        ]);

        $boliviaProject = $this->createProjectWithChain($bolivia, 'Bolivia Project');
        $usProject = $this->createProjectWithChain($unitedStates, 'US Project');

        $response = $this->getJson(self::DASHBOARD_URL . '?per_page=10', $this->headers);

        $response->assertOk();
        $projects = collect($response->json('data.projects'))->keyBy('id');

        $this->assertSame('BOB', $projects[$boliviaProject->id]['currency_code']);
        $this->assertSame('USD', $projects[$usProject->id]['currency_code']);
    }

    private function createProjectWithChain(Country $country, string $projectName = 'Sample Project'): Project
    {
        $kpa = \App\Modules\Kpa\Domain\Kpa::factory()->create();
        $countryKpa = CountryKpa::factory()->create([
            'id_country' => $country->id,
            'id_kpa' => $kpa->id,
        ]);
        $strategicOutput = StrategicOutput::factory()->create(['id_ck' => $countryKpa->id]);
        $measure = Measure::factory()->create(['strategic_output_id' => $strategicOutput->id]);
        $indicator = Indicator::factory()->create(['measure_id' => $measure->id]);

        $program = Program::factory()->create();
        ProgramCountryUserRole::factory()->create([
            'program_id' => $program->id,
            'country_user_role_id' => $this->countryUserRole->id,
        ]);

        $project = Project::factory()->create([
            'program_id' => $program->id,
            'name' => $projectName,
            'contact_id' => Contact::factory()->create()->id,
            'beneficiary_id' => Beneficiary::factory()->create()->id,
            'project_state_id' => ProjectState::factory()->create()->id,
        ]);
        $project->indicators()->attach($indicator->id);
        $project->agencies()->attach(Agency::factory()->create()->id, ['contribution' => 50]);
        $project->donors()->attach(Donor::factory()->create()->id, ['contribution' => 50]);

        return $project;
    }
}
