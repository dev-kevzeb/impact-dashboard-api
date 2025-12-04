<?php

namespace Tests\Feature;

use App\Modules\Kpa\Domain\Kpa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpaTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/kpas';

    private const ERROR_NAME_EMPTY = 'el nombre del KPA no debe ir vacio';
    private const ERROR_NAME_MIN_LENGTH = 'el nombre del KPA debe tener al menos 2 caracteres';
    private const ERROR_NAME_MAX_LENGTH = 'el nombre del KPA no debe exceder 100 caracteres';
    private const ERROR_IMPLEMENTATION_NOT_NUMERIC = 'la implementación del KPA debe ser un número';
    private const ERROR_IMPLEMENTATION_OUT_OF_RANGE = 'la implementación del KPA debe estar entre 0 y 100';

    /** LISTAR */
    public function test_can_list_kpas(): void
    {
        Kpa::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'kpas' => [
                        '*' => ['id', 'name', 'implementation']
                    ],
                    'total'
                ]
            ])
            ->assertJsonPath('data.total', 3)
            ->assertJsonCount(3, 'data.kpas');
    }

    /** LISTAR VACÍO */
    public function test_list_returns_empty_when_no_kpas(): void
    {
        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
                ->assertJsonPath('data.total', 0)
                ->assertJsonCount(0, 'data.kpas');
    }

    /** CREAR CORRECTAMENTE */
    public function test_can_create_kpa(): void
    {
        $data = [
            'name' => 'Kpa Nuevo',
            'implementation' => 50,
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertCreated()
                ->assertJson([
                    'success' => true,
                    'message' => 'KPA creado exitosamente'
                ])
                ->assertJsonStructure([
                    'data' => ['id', 'name', 'implementation']
                ]);

        $this->assertDatabaseHas('kpa', [
            'name' => 'Kpa Nuevo',
            'implementation' => 50,
        ]);
    }

    /** NAME REQUIRED */
    public function test_name_is_required(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'implementation' => 40
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name'])
                ->assertJsonPath('errors.name.0', self::ERROR_NAME_EMPTY);
    }

    /** NAME MIN LENGTH */
    public function test_name_must_be_at_least_two_characters(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'A',
            'implementation' => 40
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name'])
                ->assertJsonPath('errors.name.0', self::ERROR_NAME_MIN_LENGTH);
    }

    /** NAME MAX LENGTH */
    public function test_name_cannot_exceed_max_length(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => str_repeat('A', 101),
            'implementation' => 50
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name'])
                ->assertJsonPath('errors.name.0', self::ERROR_NAME_MAX_LENGTH);
    }

    /** IMPLEMENTATION REQUIRED */
    public function test_implementation_is_required(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Kpa válido'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['implementation'])
                ->assertJsonPath('errors.implementation.0', self::ERROR_IMPLEMENTATION_NOT_NUMERIC);
    }

    /** IMPLEMENTATION MUST BE NUMERIC */
    public function test_implementation_must_be_numeric(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Kpa válido',
            'implementation' => 'abc'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['implementation'])
                ->assertJsonPath('errors.implementation.0', self::ERROR_IMPLEMENTATION_NOT_NUMERIC);
    }

    /** IMPLEMENTATION RANGE */
    public function test_implementation_must_be_between_0_and_100(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'KPA válido',
            'implementation' => 150
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['implementation'])
                ->assertJsonPath('errors.implementation.0', self::ERROR_IMPLEMENTATION_OUT_OF_RANGE);
    }

    /** TRIM NAME */
    public function test_trims_whitespace_from_name(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => '   Kpa Limpio   ',
            'implementation' => 55
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('kpa', [
            'name' => 'Kpa Limpio'
        ]);
    }

    /** SHOW */
    public function test_can_show_kpa(): void
    {
        $kpa = Kpa::factory()->create();

        $response = $this->getJson(self::BASE_URL . "/{$kpa->id}");

        $response->assertOk()
                ->assertJson([
                    'success' => true,
                    'message' => 'KPA encontrado',
                    'data' => [
                        'id' => $kpa->id,
                        'name' => $kpa->name,
                        'implementation' => $kpa->implementation
                    ]
                ]);
    }

    /** SHOW 404 */
    public function test_returns_404_when_kpa_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/99999');

        $response->assertNotFound()
                ->assertJson(['success' => false]);
    }

    /** UPDATE */
    public function test_can_update_kpa(): void
    {
        $kpa = Kpa::factory()->create(['name' => 'Viejo', 'implementation' => 10]);

        $response = $this->putJson(self::BASE_URL . "/{$kpa->id}", [
            'name' => 'Nuevo',
            'implementation' => 90
        ]);

        $response->assertOk()
                ->assertJson([
                    'success' => true,
                    'message' => 'KPA actualizado exitosamente'
                ]);

        $this->assertDatabaseHas('kpa', [
            'id' => $kpa->id,
            'name' => 'Nuevo',
            'implementation' => 90
        ]);
    }

    /** UPDATE - TRIM */
    public function test_can_update_with_trimmed_name(): void
    {
        $kpa = Kpa::factory()->create(['name' => 'Original']);

        $response = $this->putJson(self::BASE_URL . "/{$kpa->id}", [
            'name' => '   Kpa Limpio   ',
            'implementation' => 40
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('kpa', [
            'id' => $kpa->id,
            'name' => 'Kpa Limpio'
        ]);
    }

    /** UPDATE IMPLEMENTATION INVALID */
    public function test_update_implementation_must_be_in_range(): void
    {
        $kpa = Kpa::factory()->create(['implementation' => 50]);

        $response = $this->putJson(self::BASE_URL . "/{$kpa->id}", [
            'name' => 'Valid Name',
            'implementation' => 200
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['implementation'])
                ->assertJsonPath('errors.implementation.0', self::ERROR_IMPLEMENTATION_OUT_OF_RANGE);
    }

    public function test_name_must_be_unique(): void
    {
        Kpa::factory()->create(['name' => 'KPA Existente', 'implementation' => 50]);

        $response = $this->postJson(self::BASE_URL, ['name' => 'KPA Existente', 'implementation' => 70]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name'])
                ->assertJsonPath('errors.name.0', 'Ya existe un KPA con ese nombre');
    }
}
