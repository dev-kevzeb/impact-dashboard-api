<?php

namespace Tests\Feature;

use App\Modules\Country\Domain\Country;
use App\Modules\CountryKpa\Domain\CountryKpa;
use App\Modules\Indicator\Domain\Indicator;
use App\Modules\IndicatorType\Domain\IndicatorType;
use App\Modules\Kpa\Domain\Kpa;
use App\Modules\Measure\Domain\Measure;
use App\Modules\Program\Domain\Program;
use App\Modules\Project\Domain\Project;
use App\Modules\Statistics\Service\StatisticsService;
use App\Modules\StrategicOutput\Domain\StrategicOutput;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StatisticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private StatisticsService $service;
    private Program $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(StatisticsService::class);
        $this->program = Program::factory()->create();
    }

    // ─────────────────────────────────────────────────────────────
    //  BOTTOM-UP measure implementation
    // ─────────────────────────────────────────────────────────────

    public function test_bu_measure_implementation_uses_project_weight_directly(): void
    {
        // Σ(θ_i × W_i) × 100
        // = (0.50 × 0.40 + 1.00 × 0.30) × 100
        // = (0.20 + 0.30) × 100  = 50.0
        // (sum of weights = 0.70, NOT 1 — should still work)

        $buType = IndicatorType::factory()->create(['is_bottom_up' => true]);
        $measure = Measure::factory()->create();
        $indicator = Indicator::factory()->create([
            'type_id'    => $buType->id,
            'measure_id' => $measure->id,
        ]);

        $projectA = Project::factory()->create(['progress' => 50,  'weight' => 0.40, 'program_id' => $this->program->id]);
        $projectB = Project::factory()->create(['progress' => 100, 'weight' => 0.30, 'program_id' => $this->program->id]);

        DB::table('project_indicator')->insert([
            ['project_id' => $projectA->id, 'indicator_id' => $indicator->id],
            ['project_id' => $projectB->id, 'indicator_id' => $indicator->id],
        ]);

        $result = $this->service->getMeasureImplementation($measure->id);

        $this->assertEquals(50.0, $result['implementation']);
    }

    public function test_bu_measure_implementation_with_no_projects_returns_zero(): void
    {
        $buType  = IndicatorType::factory()->create(['is_bottom_up' => true]);
        $measure = Measure::factory()->create();
        Indicator::factory()->create([
            'type_id'    => $buType->id,
            'measure_id' => $measure->id,
        ]);

        $result = $this->service->getMeasureImplementation($measure->id);

        $this->assertEquals(0, $result['implementation']);
    }

    public function test_measure_with_no_indicators_returns_zero(): void
    {
        $measure = Measure::factory()->create();

        $result = $this->service->getMeasureImplementation($measure->id);

        $this->assertEquals(0, $result['implementation']);
        $this->assertEquals($measure->name, $result['name']);
    }

    // ─────────────────────────────────────────────────────────────
    //  TOP-DOWN measure implementation
    // ─────────────────────────────────────────────────────────────

    public function test_td_measure_implementation_uses_actual_value_over_target(): void
    {
        // (60 / 80) × 100 = 75.0

        $tdType  = IndicatorType::factory()->create(['is_bottom_up' => false]);
        $measure = Measure::factory()->create();
        Indicator::factory()->create([
            'type_id'      => $tdType->id,
            'measure_id'   => $measure->id,
            'target'       => 80,
            'actual_value' => 60,
        ]);

        $result = $this->service->getMeasureImplementation($measure->id);

        $this->assertEquals(75.0, $result['implementation']);
    }

    public function test_td_measure_with_null_actual_value_returns_zero(): void
    {
        $tdType  = IndicatorType::factory()->create(['is_bottom_up' => false]);
        $measure = Measure::factory()->create();
        Indicator::factory()->create([
            'type_id'      => $tdType->id,
            'measure_id'   => $measure->id,
            'target'       => 100,
            'actual_value' => null,
        ]);

        $result = $this->service->getMeasureImplementation($measure->id);

        $this->assertEquals(0, $result['implementation']);
    }

    public function test_td_measure_averages_multiple_indicators(): void
    {
        // indicator1: 60/80 × 100 = 75
        // indicator2: 40/80 × 100 = 50
        // avg = (75 + 50) / 2 = 62.5

        $tdType  = IndicatorType::factory()->create(['is_bottom_up' => false]);
        $measure = Measure::factory()->create();

        Indicator::factory()->create([
            'name'         => 'Indicador TD uno',
            'type_id'      => $tdType->id,
            'measure_id'   => $measure->id,
            'target'       => 80,
            'actual_value' => 60,
        ]);
        Indicator::factory()->create([
            'name'         => 'Indicador TD dos',
            'type_id'      => $tdType->id,
            'measure_id'   => $measure->id,
            'target'       => 80,
            'actual_value' => 40,
        ]);

        $result = $this->service->getMeasureImplementation($measure->id);

        $this->assertEquals(62.5, $result['implementation']);
    }

    // ─────────────────────────────────────────────────────────────
    //  KPA implementation — active country filtering
    // ─────────────────────────────────────────────────────────────

    private function buildActiveCountryChain(Kpa $kpa): array
    {
        $buType  = IndicatorType::factory()->create(['is_bottom_up' => true]);
        $country = Country::factory()->create(['active' => true]);
        $ck      = CountryKpa::factory()->create(['id_country' => $country->id, 'id_kpa' => $kpa->id]);
        $so      = StrategicOutput::factory()->create(['id_ck' => $ck->id]);
        $measure = Measure::factory()->create(['strategic_output_id' => $so->id]);
        $indicator = Indicator::factory()->create(['type_id' => $buType->id, 'measure_id' => $measure->id]);
        $project = Project::factory()->create(['progress' => 100, 'weight' => 1.0, 'program_id' => $this->program->id]);
        DB::table('project_indicator')->insert([
            ['project_id' => $project->id, 'indicator_id' => $indicator->id],
        ]);
        return compact('country', 'ck', 'so', 'measure', 'indicator', 'project');
    }

    public function test_kpa_implementation_includes_active_country(): void
    {
        $kpa = Kpa::factory()->create();
        $this->buildActiveCountryChain($kpa);

        $result = $this->service->getKpaImplementation($kpa->id);

        $this->assertGreaterThan(0, $result['implementation']);
    }

    public function test_kpa_implementation_excludes_inactive_country(): void
    {
        $buType  = IndicatorType::factory()->create(['is_bottom_up' => true]);
        $kpa     = Kpa::factory()->create();
        $country = Country::factory()->create(['active' => false]);
        $ck      = CountryKpa::factory()->create(['id_country' => $country->id, 'id_kpa' => $kpa->id]);
        $so      = StrategicOutput::factory()->create(['id_ck' => $ck->id]);
        $measure = Measure::factory()->create(['strategic_output_id' => $so->id]);
        $indicator = Indicator::factory()->create(['type_id' => $buType->id, 'measure_id' => $measure->id]);
        $project = Project::factory()->create(['progress' => 100, 'weight' => 1.0, 'program_id' => $this->program->id]);
        DB::table('project_indicator')->insert([
            ['project_id' => $project->id, 'indicator_id' => $indicator->id],
        ]);

        $result = $this->service->getKpaImplementation($kpa->id);

        $this->assertEquals(0, $result['implementation']);
    }

    public function test_kpa_implementation_only_counts_active_countries(): void
    {
        $kpa = Kpa::factory()->create();

        // Active country — should be counted
        $this->buildActiveCountryChain($kpa);

        // Inactive country — should NOT be counted
        $buType    = IndicatorType::factory()->create(['is_bottom_up' => true]);
        $inactive  = Country::factory()->create(['active' => false]);
        $ckInactive = CountryKpa::factory()->create(['id_country' => $inactive->id, 'id_kpa' => $kpa->id]);
        $soInactive = StrategicOutput::factory()->create(['id_ck' => $ckInactive->id]);
        $mInactive  = Measure::factory()->create(['strategic_output_id' => $soInactive->id]);
        $indInactive = Indicator::factory()->create(['type_id' => $buType->id, 'measure_id' => $mInactive->id]);
        $projInactive = Project::factory()->create(['progress' => 0, 'weight' => 1.0, 'program_id' => $this->program->id]);
        DB::table('project_indicator')->insert([
            ['project_id' => $projInactive->id, 'indicator_id' => $indInactive->id],
        ]);

        $result = $this->service->getKpaImplementation($kpa->id);

        // Only the active country (100% progress) counts, inactive (0%) is excluded
        $this->assertEquals(100.0, $result['implementation']);
    }

    // ─────────────────────────────────────────────────────────────
    //  Country-KPA implementation — active country filtering
    // ─────────────────────────────────────────────────────────────

    public function test_country_kpa_implementation_includes_active_country(): void
    {
        $kpa  = Kpa::factory()->create();
        $data = $this->buildActiveCountryChain($kpa);

        $result = $this->service->getCountryKpaImplementation($data['country']->id, $kpa->id);

        $this->assertGreaterThan(0, $result['implementation']);
    }

    public function test_country_kpa_implementation_excludes_inactive_country(): void
    {
        $buType  = IndicatorType::factory()->create(['is_bottom_up' => true]);
        $kpa     = Kpa::factory()->create();
        $country = Country::factory()->create(['active' => false]);
        $ck      = CountryKpa::factory()->create(['id_country' => $country->id, 'id_kpa' => $kpa->id]);
        $so      = StrategicOutput::factory()->create(['id_ck' => $ck->id]);
        $measure = Measure::factory()->create(['strategic_output_id' => $so->id]);
        $indicator = Indicator::factory()->create(['type_id' => $buType->id, 'measure_id' => $measure->id]);
        $project = Project::factory()->create(['progress' => 100, 'weight' => 1.0, 'program_id' => $this->program->id]);
        DB::table('project_indicator')->insert([
            ['project_id' => $project->id, 'indicator_id' => $indicator->id],
        ]);

        $result = $this->service->getCountryKpaImplementation($country->id, $kpa->id);

        $this->assertEquals(0, $result['implementation']);
    }
}
