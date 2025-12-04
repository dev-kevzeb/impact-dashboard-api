<?php

namespace Tests\Feature;

use App\Modules\IndicatorType\Domain\IndicatorType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndicatorTypeTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/indicator-types';
    private const ERROR_NAME_EMPTY = 'el nombre del tipo de indicador no debe ir vacio';
    private const ERROR_NAME_MIN_LENGTH = 'el nombre del tipo de indicador debe tener al menos 2 caracteres';
    private const ERROR_NAME_MAX_LENGTH = 'el nombre del tipo de indicador no debe exceder 100 caracteres';
    private const ERROR_NAME_UNIQUE = 'Ya existe un tipo de indicador con ese nombre';

    private const ERROR_STRING = 'El nombre debe ser una cadena de texto';

    public function test_can_list_indicator_types(): void
    {
        IndicatorType::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data'=>[
                'indicator_types' => [
                    '*' => ['id', 'name']
                ],
                'total'
            ]
        ])
        ->assertJsonPath('data.total', 3)
        ->assertJsonCount(3, 'data.indicator_types');
    }

     //LISTAR VACÍO
    public function test_list_returns_empty_when_no_indicator_types(): void
    {
        $response = $this->getJson(self::BASE_URL);
        $response->assertOk()
                 ->assertJsonPath('data.total', 0)
                 ->assertJsonCount(0, 'data.indicator_types');
    }

    // CREACION CON DATOS VALIDOS

    public function test_can_create_indicator_type(): void
    {
        $data = ['name' => 'valid indicator type'];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertCreated()
            ->assertJson([
                'success'=> true,
                'message' => "Tipo de indicador creado exitosamente",
            ])
            ->assertJsonStructure([
                'data' => ['id', 'name']
            ]);
        
        $this->assertDatabaseHas('indicator_type', [
            'name' => 'valid indicator type'
        ]);
    }

    // ERROR POR AUSENCIA DE CAMPOS

    public function test_name_is_required(): void
    {
        $response = $this->postJson(self::BASE_URL, []);

        $response->assertStatus(422)
        ->assertJsonValidationErrors(['name'])
        ->assertJsonPath('errors.name.0',self::ERROR_NAME_EMPTY);
    }
 
    // TAMAÑO MINIMO DE CARACTERES

    public function test_name_must_be_at_least_two_characters(): void
    {
        $response = $this->postJson(self::BASE_URL, [ 'name' => 'A']);

        $response->assertStatus(422)
        ->assertJsonValidationErrors(['name'])
        ->assertJsonPath('errors.name.0',self::ERROR_NAME_MIN_LENGTH);
    }

    // NOMBRE QUE YA EXISRTE 

    public function test_name_must_be_unique(): void
    {
        IndicatorType::factory()->create(['name'=> 'Tipo de indicador Existente']);

        $data = ['name'=> 'Tipo de indicador Existente'];
        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name'])
                ->assertJsonPath('errors.name.0', self::ERROR_NAME_UNIQUE);
    }

    public function test_name_cannot_exceed_max_length(): void
    {
         $data = ['name' => str_repeat('A', 256)];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name'])
                 ->assertJsonPath('errors.name.0', self::ERROR_NAME_MAX_LENGTH);
    }

     /** CREAR - TRIM */
    public function test_trims_whitespace_from_name(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => '   Nuevo Indicador   '
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('indicator_type', [
            'name' => 'Nuevo Indicador'
        ]);
    }
     /** CREAR - SOLO ESPACIOS */
    public function test_name_cannot_be_only_whitespace(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => '   '
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    public function test_name_must_be_string_not_number(): void
    {
        $response = $this->postJson(self::BASE_URL, ['name' => 12345]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name'])
                 ->assertJsonPath('errors.name.0', self::ERROR_STRING);
    }

    public function test_can_show_indicator_type(): void
    {
        $indicatorType = IndicatorType::factory()->create();

        $response= $this->getJson(self::BASE_URL."/{$indicatorType->id}");

        $response->assertOk()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Tipo de indicador encontrado',
                     'data' => [
                         'id' => $indicatorType->id,
                         'name' => $indicatorType->name
                     ]
                 ]);
    }

     public function test_can_update_indicator_type(): void
    {
        $indicatorType = IndicatorType::factory()->create(['name'=> 'Original']);

        $response = $this->putJson(self::BASE_URL."/$indicatorType->id", [
            'name' => 'Actualizado'
        ]);

        $response->assertOk()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Tipo de indicador actualizado exitosamente'
                ]);

        $this->assertDatabaseHas('indicator_type', [
            'id' => $indicatorType->id,
            'name' => 'Actualizado'
        ]);
    }

    public function test_cannot_update_with_duplicate_name(): void
    {
        $t1 = IndicatorType::factory()->create(['name'=> 'Tipo 1']);
        $t2 = IndicatorType::factory()->create(['name'=> 'Tipo 2']);

        $response = $this->putJson(self::BASE_URL."/$t2->id", [
            'name' => 'Tipo 1'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name'])
                 ->assertJsonPath('errors.name.0', self::ERROR_NAME_UNIQUE);
    }

    public function test_can_update_with_trimmed_name(): void
    {
        $indicatorType = IndicatorType::factory()->create(['name'=> 'Algo']);

        $response = $this->putJson(self::BASE_URL."/$indicatorType->id", [
            'name' => '   Nombre Limpio   '
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('indicator_type', [
            'id' => $indicatorType->id,
            'name' => 'Nombre Limpio'
        ]);
    }

    public function test_name_must_be_string_on_update(): void
    {
        $indicatorType = IndicatorType::factory()->create();

        $response = $this->putJson(self::BASE_URL."/$indicatorType->id", [
            'name' => 12345
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name'])
                 ->assertJsonPath('errors.name.0', self::ERROR_STRING);
    }
} 