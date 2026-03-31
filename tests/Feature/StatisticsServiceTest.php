<?php

namespace Tests\Feature;

use App\Modules\Indicator\Domain\Indicator;
use App\Modules\IndicatorType\Domain\IndicatorType;
use App\Modules\Measure\Domain\Measure;
use App\Modules\Program\Domain\Program;
use App\Modules\Project\Domain\Project;
use App\Modules\Statistics\Service\StatisticsService;
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
}
