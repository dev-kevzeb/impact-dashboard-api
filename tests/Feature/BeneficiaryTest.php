<?php

namespace Tests\Feature;

use App\Modules\Beneficiary\Domain\Beneficiary;
use App\Modules\Program\Domain\Program;
use App\Modules\Project\Domain\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeneficiaryTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/beneficiaries';
    private const ERROR_REQUIRED = 'Beneficiary name is required.';
    private const ERROR_UNIQUE = 'This beneficiary already exists in the system.';
    private const ERROR_MIN_LENGTH = 'Beneficiary name must be at least 2 characters long.';
    private const ERROR_MAX_LENGTH = 'Name must not exceed 255 characters.';
    private const ERROR_STRING = 'The name must be a text string.';

    /**
     * Test: GET /api/v1/beneficiaries
     * Debe listar todos los beneficiarios con el total correcto
     */
    public function test_can_list_beneficiaries(): void
    {
        Beneficiary::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL, $this->authHeaders());

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

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertCreated()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Beneficiary created successfully',
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

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

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

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

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
        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

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

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

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

        $response = $this->getJson(self::BASE_URL . "/{$beneficiary->id}", $this->authHeaders());

        $response->assertOk()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Beneficiary found',
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

        $response = $this->putJson(self::BASE_URL . "/{$beneficiary->id}", $data, $this->authHeaders());

        $response->assertOk()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Beneficiary uploaded successfully',
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
        Beneficiary::factory()->create(['name' => 'Beneficiario 1']);
        $beneficiary2 = Beneficiary::factory()->create(['name' => 'Beneficiario 2']);

        $data = ['name' => 'Beneficiario 1'];
        $response = $this->putJson(self::BASE_URL . "/{$beneficiary2->id}", $data, $this->authHeaders());

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
        $response = $this->getJson(self::BASE_URL . '/99999', $this->authHeaders());

        $response->assertNotFound()
                 ->assertJson(['success' => false]);
    }

    /**
     * Test: PUT /api/v1/beneficiaries/{id}
     * Debe retornar 404 cuando se intenta actualizar un beneficiario inexistente
     */
    public function test_returns_404_when_updating_non_existent_beneficiary(): void
    {
        $response = $this->putJson(self::BASE_URL . '/99999', ['name' => 'Nombre Cualquiera'], $this->authHeaders());

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

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

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

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

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
            ['name' => 'Mismo Nombre'],
            $this->authHeaders()
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

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

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
        $response = $this->getJson(self::BASE_URL, $this->authHeaders());

        $response->assertOk()
                 ->assertJsonPath('data.total', 0)
                 ->assertJsonCount(0, 'data.beneficiaries');
    }

    public function test_can_delete_beneficiary_without_relations(): void
    {
        $beneficiary = Beneficiary::factory()->create();

        $response = $this->deleteJson(self::BASE_URL . "/{$beneficiary->id}", [], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('message', 'Beneficiary deleted successfully');

        $this->assertDatabaseMissing('beneficiary', ['id' => $beneficiary->id]);
    }

    public function test_cannot_delete_beneficiary_with_project_relations(): void
    {
        $beneficiary = Beneficiary::factory()->create();
        $program = Program::factory()->create();

        Project::factory()->create([
            'program_id' => $program->id,
            'beneficiary_id' => $beneficiary->id,
        ]);

        $response = $this->deleteJson(self::BASE_URL . "/{$beneficiary->id}", [], $this->authHeaders());

        $response->assertStatus(409)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('beneficiary', ['id' => $beneficiary->id]);
    }

    public function test_delete_returns_not_found_for_non_existent_beneficiary(): void
    {
        $response = $this->deleteJson(self::BASE_URL . '/999999', [], $this->authHeaders());

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Beneficiary not Found');
    }
}
