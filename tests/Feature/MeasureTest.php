<?php

namespace Tests\Feature;

use App\Modules\Measure\Domain\Measure;
use App\Modules\StrategicOutput\Domain\StrategicOutput;
use App\Modules\Indicator\Domain\Indicator;
use App\Modules\IndicatorType\Domain\IndicatorType;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeasureTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/measures';

    private const ERROR_NAME_EMPTY = 'The name of the measure should not be empty';
    private const ERROR_NAME_MIN   = 'Measure name must be at least 2 characters';
    private const ERROR_NAME_MAX   = 'The measure name must not exceed 150 characters';

    private const ERROR_SO_REQUIRED = 'The strategic result is mandatory';
    private const ERROR_SO_NOT_FOUND = 'The specified strategic result does not exist';

    private const ERROR_INDICATOR_DUPLICATED = 'duplicate indicators are not allowed in the measure';


    // LISTAR

    public function test_can_list_measures(): void
    {
        Measure::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'measures' => [
                        '*' => ['id', 'name']
                    ],
                    'total'
                ]
            ])
            ->assertJsonPath('data.total', 3)
            ->assertJsonCount(3, 'data.measures');
    }

    public function test_list_returns_empty_when_no_measures(): void
    {
        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
            ->assertJsonPath('data.total', 0)
            ->assertJsonCount(0, 'data.measures');
    }


    // CREAR MEDIDA

    public function test_can_create_measure(): void
    {
        $so = StrategicOutput::factory()->create();

        $data = [
            'name' => 'Nueva Medida',
            'strategic_output_id' => $so->id
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Measure created successfully',
            ])
            ->assertJsonStructure([
                'data' => ['id', 'name']
            ]);

        $this->assertDatabaseHas('measure', [
            'name' => 'Nueva Medida',
            'strategic_output_id' => $so->id
        ]);
    }

    public function test_name_is_required_on_create(): void
    {
        $so = StrategicOutput::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'strategic_output_id' => $so->id
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonPath('errors.name.0', self::ERROR_NAME_EMPTY);
    }

    public function test_name_too_short(): void
    {
        $so = StrategicOutput::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'name' => 'A',
            'strategic_output_id' => $so->id
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.name.0', self::ERROR_NAME_MIN);
    }

    public function test_name_too_long(): void
    {
        $so = StrategicOutput::factory()->create();

        $long = str_repeat('A', 200);

        $response = $this->postJson(self::BASE_URL, [
            'name' => $long,
            'strategic_output_id' => $so->id
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.name.0', self::ERROR_NAME_MAX);
    }

    public function test_strategic_output_required(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Medida válida'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['strategic_output_id'])
            ->assertJsonPath('errors.strategic_output_id.0', self::ERROR_SO_REQUIRED);
    }

    public function test_strategic_output_must_exist(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Medida válida',
            'strategic_output_id' => 999
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.strategic_output_id.0', self::ERROR_SO_NOT_FOUND);
    }


    // SHOW

    public function test_can_show_measure(): void
    {
        $measure = Measure::factory()->create();

        $response = $this->getJson(self::BASE_URL . "/{$measure->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Measure found',
                'data' => [
                    'id' => $measure->id,
                    'name' => $measure->name,
                ]
            ]);
    }

    public function test_show_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/9999');

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }


    // UPDATE

    public function test_can_update_measure(): void
    {
        $measure = Measure::factory()->create();
        $so = StrategicOutput::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$measure->id}", [
            'name' => 'Actualizado',
            'strategic_output_id' => $so->id
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Actualizado');

        $this->assertDatabaseHas('measure', [
            'id' => $measure->id,
            'name' => 'Actualizado',
            'strategic_output_id' => $so->id
        ]);
    }

    public function test_update_requires_name_and_so(): void
    {
        $measure = Measure::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$measure->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'strategic_output_id']);
    }


    // SHOW WITH INDICATORS

    public function test_can_show_measure_with_indicators(): void
    {
        $measure = Measure::factory()->create();
        Indicator::factory()->count(2)->for($measure)->create();

        $response = $this->getJson(self::BASE_URL . "-indicators/{$measure->id}");

        $response->assertOk()
            ->assertJsonPath('data.indicators_count', 2)
            ->assertJsonCount(2, 'data.indicators');
    }


    // ADD INDICATOR

    public function test_can_add_indicator(): void
    {
        $measure = Measure::factory()->create();
        $type = IndicatorType::factory()->create();

        $response = $this->postJson(self::BASE_URL . '-indicators', [
            'name' => 'Nuevo Indicador',
            'measure_id' => $measure->id,
            'target' => 50,
            'type_id' => $type->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.indicators.0.name', 'Nuevo Indicador');

        $this->assertDatabaseHas('indicator', [
            'name' => 'Nuevo Indicador',
            'measure_id' => $measure->id,
        ]);
    }

    public function test_add_indicator_fails_if_duplicate(): void
    {
        $measure = Measure::factory()->create();
        $type = IndicatorType::factory()->create();

        Indicator::factory()->create([
            'name' => 'Duplicado',
            'measure_id' => $measure->id,
            'type_id' => $type->id,
            'target' => 30
        ]);

        $response = $this->postJson(self::BASE_URL . '-indicators', [
            'name' => 'Duplicado',
            'measure_id' => $measure->id,
            'target' => 30,
            'type_id' => $type->id,
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('message', self::ERROR_INDICATOR_DUPLICATED);
    }


    // REMOVE INDICATOR

    public function test_can_remove_indicator(): void
    {
        $measure = Measure::factory()->create();

        $indicator = Indicator::factory()->for($measure)->create();

        $response = $this->postJson(self::BASE_URL . '/remove-indicator', [
            'measure_id' => $measure->id,
            'indicator_id' => $indicator->id
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Indicator successfully removed');

        $this->assertDatabaseMissing('indicator', [
            'id' => $indicator->id,
            'measure_id' => $measure->id
        ]);
    }

    // SEARCH

    public function test_can_search_measure_by_name(): void
    {
        Measure::factory()->create(['name' => 'Medida Especial']);

        $response = $this->getJson(self::BASE_URL . '/search?name=Especial');

        $response->assertOk()
            ->assertJsonPath('data.measure.name', 'Medida Especial');
    }

    public function test_search_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/search?name=xyz');

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }
}
