<?php

namespace Tests\Feature;

use App\Modules\CountryKpa\Domain\CountryKpa;
use App\Modules\Indicator\Domain\Indicator;
use App\Modules\IndicatorType\Domain\IndicatorType;
use App\Modules\Kpa\Domain\Kpa;
use App\Modules\Measure\Domain\Measure;
use App\Modules\Program\Domain\Program;
use App\Modules\Project\Domain\Project;
use App\Modules\StrategicOutput\Domain\StrategicOutput;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CountryKpaImplementationTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_dashboard_implementation_returns_nested_tree_with_indicators(): void
    {
        $auth = $this->authHeadersWithCountry('country-manager');
        $countryId = $auth['countryUserRole']->country_id;

        $kpa = Kpa::factory()->create();
        $countryKpa = CountryKpa::factory()->create([
            'id_country' => $countryId,
            'id_kpa' => $kpa->id,
        ]);

        $strategicOutput = StrategicOutput::factory()->create(['id_ck' => $countryKpa->id]);
        $measure = Measure::factory()->create(['strategic_output_id' => $strategicOutput->id]);
        $buType = IndicatorType::factory()->create(['is_bottom_up' => true]);
        $indicator = Indicator::factory()->create([
            'type_id' => $buType->id,
            'measure_id' => $measure->id,
        ]);
        $program = Program::factory()->create();
        $project = Project::factory()->create([
            'progress' => 50,
            'weight' => 0.4,
            'program_id' => $program->id,
        ]);

        DB::table('project_indicator')->insert([
            ['project_id' => $project->id, 'indicator_id' => $indicator->id],
        ]);

        $response = $this->getJson("/api/v1/country_kpas/country/{$countryId}/implementation", $auth['headers']);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.country.id', $countryId)
            ->assertJsonPath('data.kpas.0.id_kpa', $kpa->id)
            ->assertJsonPath('data.kpas.0.implementation', 20)
            ->assertJsonPath('data.kpas.0.strategic_outputs.0.measures.0.indicators.0.implementation', 20);
    }
}