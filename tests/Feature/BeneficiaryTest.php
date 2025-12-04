<?php

namespace Tests\Feature;

use App\Modules\Beneficiary\Domain\Beneficiary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeneficiaryTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/beneficiaries';
    private const ERROR_REQUIRED = 'el nombre del beneficiario es obligatorio.';
    private const ERROR_UNIQUE = 'este beneficiario ya existe en el sistema.';
    private const ERROR_MIN_LENGTH = 'El nombre del beneficiario debe tener al menos 2 caracteres.';
    private const ERROR_MAX_LENGTH = 'el nombre no debe exceder 255 caracteres.';
    private const ERROR_STRING = 'el nombre debe ser una cadena de texto.';

    /**
     * Test: GET /api/v1/beneficiaries
     * Debe listar todos los beneficiarios con el total correcto
     */
    public function test_can_list_beneficiaries(): void
    {
        Beneficiary::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'beneficiaries' => [
                             '*' => ['id', 'name']
                         ],
                         'total'
                     ]
                 ])
                 ->assertJsonPath('data.total', 3)
                 ->assertJsonCount(3, 'data.beneficiaries');
    }

    /**
     * Test: POST /api/v1/beneficiaries
     * Debe crear un nuevo beneficiario con datos válidos
     */
    public function test_can_create_beneficiary(): void
    {
        $data = ['name' => 'Nuevo Beneficiario'];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertCreated()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Beneficiario creado exitosamente',
                 ])
                 ->assertJsonStructure([
                     'data' => ['id', 'name']
                 ]);

        $this->assertDatabaseHas('beneficiary', [
            'name' => 'Nuevo Beneficiario'
        ]);
    }

    /**
     * Test: POST /api/v1/beneficiaries
     * Debe fallar si el campo name no está presente
     */
    public function test_name_is_required(): void
    {
        $data = [];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name'])
                 ->assertJsonPath('errors.name.0', self::ERROR_REQUIRED);
    }

    /**
     * Test: POST /api/v1/beneficiaries
     * Debe fallar si el name tiene menos de 2 caracteres
     */
    public function test_name_must_be_at_least_2_characters(): void
    {
        $data = ['name' => 'A'];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name'])
                 ->assertJsonPath('errors.name.0', self::ERROR_MIN_LENGTH);
    }

    /**
     * Test: POST /api/v1/beneficiaries
     * Debe fallar si el name ya existe
     */
    public function test_name_must_be_unique(): void
    {
        Beneficiary::factory()->create(['name' => 'Beneficiario Existente']);

        $data = ['name' => 'Beneficiario Existente'];
        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name'])
                 ->assertJsonPath('errors.name.0', self::ERROR_UNIQUE);
    }

    /**
     * Test: POST /api/v1/beneficiaries
     * Debe fallar si el name excede el límite máximo
     */
    public function test_name_cannot_exceed_max_length(): void
    {
        $data = ['name' => str_repeat('A', 256)];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name'])
                 ->assertJsonPath('errors.name.0', self::ERROR_MAX_LENGTH);
    }

    /**
     * Test: GET /api/v1/beneficiaries/{id}
     * Debe mostrar un beneficiario específico
     */
    public function test_can_show_beneficiary(): void
    {
        $beneficiary = Beneficiary::factory()->create();

        $response = $this->getJson(self::BASE_URL . "/{$beneficiary->id}");

        $response->assertOk()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Beneficiario encontrado',
                     'data' => [
                         'id' => $beneficiary->id,
                         'name' => $beneficiary->name
                     ]
                 ]);
    }

    /**
     * Test: PUT /api/v1/beneficiaries/{id}
     * Debe actualizar un beneficiario existente
     */
    public function test_can_update_beneficiary(): void
    {
        $beneficiary = Beneficiary::factory()->create(['name' => 'Nombre Original']);
        $data = ['name' => 'Nombre Actualizado'];

        $response = $this->putJson(self::BASE_URL . "/{$beneficiary->id}", $data);

        $response->assertOk()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Beneficiario actualizado exitosamente',
                 ]);

        $this->assertDatabaseHas('beneficiary', [
            'id' => $beneficiary->id,
            'name' => 'Nombre Actualizado'
        ]);

        $this->assertDatabaseMissing('beneficiary', [
            'id' => $beneficiary->id,
            'name' => 'Nombre Original'
        ]);
    }

    /**
     * Test: PUT /api/v1/beneficiaries/{id}
     * Debe fallar si se intenta actualizar con un nombre duplicado
     */
    public function test_cannot_update_with_duplicate_name(): void
    {
        $beneficiary1 = Beneficiary::factory()->create(['name' => 'Beneficiario 1']);
        $beneficiary2 = Beneficiary::factory()->create(['name' => 'Beneficiario 2']);

        $data = ['name' => 'Beneficiario 1'];
        $response = $this->putJson(self::BASE_URL . "/{$beneficiary2->id}", $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name'])
                 ->assertJsonPath('errors.name.0', self::ERROR_UNIQUE);
    }

    /**
     * Test: GET /api/v1/beneficiaries/{id}
     * Debe retornar 404 cuando el beneficiario no existe
     */
    public function test_returns_404_when_beneficiary_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/99999');

        $response->assertNotFound()
                 ->assertJson(['success' => false]);
    }

    /**
     * Test: PUT /api/v1/beneficiaries/{id}
     * Debe retornar 404 cuando se intenta actualizar un beneficiario inexistente
     */
    public function test_returns_404_when_updating_non_existent_beneficiary(): void
    {
        $response = $this->putJson(self::BASE_URL . '/99999', ['name' => 'Nombre Cualquiera']);

        $response->assertNotFound()
                 ->assertJson(['success' => false]);
    }

    /**
     * Test: POST /api/v1/beneficiaries
     * Debe hacer trim de espacios en blanco
     */
    public function test_trims_whitespace_from_name(): void
    {
        $data = ['name' => '  Beneficiario con Espacios  '];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertCreated();
        $this->assertDatabaseHas('beneficiary', [
            'name' => 'Beneficiario con Espacios'
        ]);
    }

    /**
     * Test: POST /api/v1/beneficiaries
     * Debe fallar con solo espacios en blanco
     */
    public function test_name_cannot_be_only_whitespace(): void
    {
        $data = ['name' => '   '];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test: PUT /api/v1/beneficiaries/{id}
     * Debe permitir actualizar con el mismo nombre
     */
    public function test_can_update_beneficiary_with_same_name(): void
    {
        $beneficiary = Beneficiary::factory()->create(['name' => 'Mismo Nombre']);

        $response = $this->putJson(
            self::BASE_URL . "/{$beneficiary->id}",
            ['name' => 'Mismo Nombre']
        );

        $response->assertOk();
        $this->assertDatabaseHas('beneficiary', [
            'id' => $beneficiary->id,
            'name' => 'Mismo Nombre'
        ]);
    }

    /**
     * Test: POST /api/v1/beneficiaries
     * Debe fallar con nombre de tipo incorrecto
     */
    public function test_name_must_be_string_not_number(): void
    {
        $data = ['name' => 12345];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name'])
                 ->assertJsonPath('errors.name.0', self::ERROR_STRING);
    }

    /**
     * Test: GET /api/v1/beneficiaries
     * Debe retornar lista vacía cuando no hay beneficiarios
     */
    public function test_list_returns_empty_when_no_beneficiaries(): void
    {
        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
                 ->assertJsonPath('data.total', 0)
                 ->assertJsonCount(0, 'data.beneficiaries');
    }
}
