<?php

namespace Tests\Feature;

use App\Modules\Country\Domain\Country;
use App\Modules\CountryKpa\Domain\CountryKpa;
use App\Modules\Kpa\Domain\Kpa;
use App\Modules\Measure\Domain\Measure;
use App\Modules\StrategicOutput\Domain\StrategicOutput;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StrategicOutputTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/strategic-outputs';

    /** LISTAR TODOS */
    public function test_can_list_strategic_outputs(): void
    {
        StrategicOutput::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
                 ->assertJsonPath('data.total', 3)
                 ->assertJsonCount(3, 'data.strategic_outputs');
    }

    /** LISTAR VACÍO */
    public function test_list_returns_empty_when_none(): void
    {
        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
                 ->assertJsonPath('data.total', 0)
                 ->assertJsonCount(0, 'data.strategic_outputs');
    }

    /** CREAR */
    public function test_can_create_strategic_output(): void
    {
        $country = Country::factory()->create();
        $kpa = Kpa::factory()->create();
        $ck = CountryKpa::factory()->create([
            'id_country' => $country->id,
            'id_kpa' => $kpa->id
        ]);

        $data = [
            'name' => 'Nuevo Resultado',
            'id_ck' => $ck->id
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertCreated()
                 ->assertJsonPath('message', 'Resultado estratégico creado exitosamente');

        $this->assertDatabaseHas('strategic_output', [
            'name' => 'Nuevo Resultado',
            'id_ck' => $ck->id
        ]);
    }

    /** VALIDACIÓN — NAME REQUERIDO */
    public function test_name_is_required(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'id_ck' => 1
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    /** VALIDACIÓN — NAME MIN */
    public function test_name_must_be_at_least_2_characters(): void
    {
        $country = Country::factory()->create();
        $kpa = Kpa::factory()->create();
        $ck = CountryKpa::factory()->create([
            'id_country' => $country->id,
            'id_kpa' => $kpa->id
        ]);

        $response = $this->postJson(self::BASE_URL, [
            'name' => 'A',
            'id_ck' => $ck->id
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    /** SHOW */
    public function test_can_show_strategic_output(): void
    {
        $so = StrategicOutput::factory()->create();

        $response = $this->getJson(self::BASE_URL . "/{$so->id}");

        $response->assertOk()
                 ->assertJsonPath('data.id', $so->id)
                 ->assertJsonPath('data.name', $so->name);
    }

    /** SHOW 404 */
    public function test_show_returns_404_when_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/99999');

        $response->assertNotFound();
    }

    /** UPDATE */
    public function test_can_update_strategic_output(): void
    {
        $so = StrategicOutput::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$so->id}", [
            'name' => 'Actualizado',
            'id_ck' => $so->id_ck
        ]);

        $response->assertOk()
                 ->assertJsonPath('message', 'Resultado estratégico actualizado exitosamente');

        $this->assertDatabaseHas('strategic_output', [
            'id' => $so->id,
            'name' => 'Actualizado'
        ]);
    }

    /** UPDATE*/
    public function test_update_fails_with_invalid_name(): void
    {
        $so = StrategicOutput::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$so->id}", [
            'name' => 'A',
            'id_ck' => $so->id_ck
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    /** UPDATE — ID_CK requerido */
    public function test_update_requires_id_ck(): void
    {
        $so = StrategicOutput::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$so->id}", [
            'name' => 'Solo Nombre'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['id_ck']);
    }


    /** ADD MEASURE */
    public function test_can_add_measure_to_strategic_output(): void
    {
        $so = StrategicOutput::factory()->create();

        $response = $this->postJson(self::BASE_URL . '-measures', [
            'strategic_output_id' => $so->id,
            'name' => 'Nueva Medida'
        ]);

        $response->assertOk()
                 ->assertJsonPath('message', 'Medida agregada exitosamente al resultado estratégico');

        $this->assertDatabaseHas('measure', [
            'name' => 'Nueva Medida',
            'strategic_output_id' => $so->id
        ]);
    }

    /** ADD MEASURE DUPLICADA */
    public function test_cannot_add_duplicate_measure(): void
    {
        $so = StrategicOutput::factory()->create();

        $so->measures()->create(['name' => 'Duplicada']);

        $response = $this->postJson(self::BASE_URL . '-measures', [
            'strategic_output_id' => $so->id,
            'name' => 'Duplicada'
        ]);

        $response->assertStatus(400);
    }

    /** REMOVE MEASURE */
    public function test_can_remove_measure(): void
    {
        $so = StrategicOutput::factory()->create();

        $measure = $so->measures()->create(['name' => 'Eliminar']);

        $response = $this->postJson(self::BASE_URL . '/remove-measure', [
            'strategic_output_id' => $so->id,
            'measure_id' => $measure->id
        ]);

        $response->assertOk();

        $this->assertDatabaseMissing('measure', [
            'id' => $measure->id
        ]);
    }

    /** REMOVE — Medida NO pertenece */
    public function test_cannot_remove_measure_not_belonging(): void
    {
        $so1 = StrategicOutput::factory()->create();
        $so2 = StrategicOutput::factory()->create();

        $measure = $so2->measures()->create(['name' => 'Ajena']);

        $response = $this->postJson(self::BASE_URL . '/remove-measure', [
            'strategic_output_id' => $so1->id,
            'measure_id' => $measure->id
        ]);

        $response->assertStatus(400);
    }

    /** SEARCH */
    public function test_can_search_by_name(): void
    {
        $so = StrategicOutput::factory()->create(['name' => 'Objetivo Mayor']);

        $response = $this->getJson(self::BASE_URL . '/search?name=Objetivo');

        $response->assertOk()
                 ->assertJsonPath('data.id', $so->id);
    }
}
