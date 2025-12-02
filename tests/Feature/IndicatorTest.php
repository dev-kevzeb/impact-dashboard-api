<?php

namespace Tests\Feature;

use App\Modules\Indicator\Domain\Indicator;
use App\Modules\IndicatorType\Domain\IndicatorType;
use App\Modules\Measure\Domain\Measure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndicatorTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/indicators';

    private IndicatorType $type;
    private Measure $measure;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = IndicatorType::factory()->create();
        $this->measure = Measure::factory()->create();
    }

    // LISTAR
  
    public function test_can_list_indicators(): void
    {
        Indicator::factory()->count(3)->create([
            'type_id' => $this->type->id,
            'measure_id' => $this->measure->id,
        ]);

        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
            ->assertJsonPath('data.total', 3)
            ->assertJsonCount(3, 'data.indicators');
    }

    public function test_list_returns_empty_when_no_indicators(): void
    {
        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
            ->assertJsonPath('data.total', 0)
            ->assertJsonCount(0, 'data.indicators');
    }


    // CREAR


    public function test_can_create_indicator(): void
    {
        $data = [
            'name' => 'Indicador Nuevo',
            'target' => 50,
            'type_id' => $this->type->id,
            'measure_id' => $this->measure->id,
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Indicador Nuevo');

        $this->assertDatabaseHas('indicator', [
            'name' => 'Indicador Nuevo',
            'target' => 50,
        ]);
    }

    public function test_name_is_required(): void
    {
        $data = [
            'target' => 20,
            'type_id' => $this->type->id,
            'measure_id' => $this->measure->id,
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_target_must_be_positive_number(): void
    {
        $data = [
            'name' => 'Test',
            'target' => -5,
            'type_id' => $this->type->id,
            'measure_id' => $this->measure->id,
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['target']);
    }

    public function test_type_id_must_exist(): void
    {
        $data = [
            'name' => 'Test',
            'target' => 20,
            'type_id' => 999,
            'measure_id' => $this->measure->id,
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type_id']);
    }

    public function test_measure_id_must_exist(): void
    {
        $data = [
            'name' => 'Test',
            'target' => 20,
            'type_id' => $this->type->id,
            'measure_id' => 999,
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['measure_id']);
    }


     //  MOSTRAR


    public function test_can_show_indicator(): void
    {
        $indicator = Indicator::factory()->create([
            'type_id' => $this->type->id,
            'measure_id' => $this->measure->id,
        ]);

        $response = $this->getJson(self::BASE_URL . "/{$indicator->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $indicator->id);
    }

    public function test_show_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . "/9999");

        $response->assertStatus(404);
    }


    //  ACTUALIZAR

    public function test_can_update_indicator(): void
    {
        $indicator = Indicator::factory()->create([
            'type_id' => $this->type->id,
            'measure_id' => $this->measure->id,
        ]);

        $response = $this->putJson(self::BASE_URL . "/{$indicator->id}", [
            'name' => 'Actualizado',
            'target' => 80,
            'type_id' => $this->type->id,
            'measure_id' => $this->measure->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Actualizado');

        $this->assertDatabaseHas('indicator', [
            'id' => $indicator->id,
            'name' => 'Actualizado',
            'target' => 80
        ]);
    }

    public function test_update_requires_valid_data(): void
    {
        $indicator = Indicator::factory()->create([
            'type_id' => $this->type->id,
            'measure_id' => $this->measure->id,
        ]);

        $response = $this->putJson(self::BASE_URL . "/{$indicator->id}", [
            'name' => '',
            'target' => 0,
            'type_id' => 0,
            'measure_id' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'target', 'type_id', 'measure_id']);
    }

    /** ============================
     *  BUSCAR
     *  ============================ */

    public function test_can_search_indicator_by_name(): void
    {
        Indicator::factory()->create([
            'name' => 'Salud Pública',
            'type_id' => $this->type->id,
            'measure_id' => $this->measure->id,
        ]);

        $response = $this->getJson(self::BASE_URL . "/search?name=salud");

        $response->assertOk()
            ->assertJsonPath('data.name', 'Salud Pública');
    }

    public function test_search_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . "/search?name=xxxxxxxx");

        $response->assertStatus(404);
    }
}
