<?php

namespace Tests\Feature;

use App\Modules\Donor\Domain\Donor;
use App\Modules\Program\Domain\Program;
use App\Modules\Project\Domain\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonorTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/donors';
    private const ERROR_REQUIRED = 'The donor name is required.';
    private const ERROR_UNIQUE = 'This donor already exists in the system.';
    private const ERROR_MIN_LENGTH = 'The donor name must be at least 2 characters long.';
    private const ERROR_MAX_LENGTH = 'The donor name must not exceed 255 characters.';
    private const ERROR_STRING = 'The name must only contain letters and spaces.';

    /**
     * Test: GET /api/v1/donors
     * Debe listar todos los donantes con el total correcto
     */
    public function test_can_list_donors(): void
    {
        Donor::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL, $this->authHeaders());

        $response->assertOk()
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'donors' => [
                             '*' => ['id', 'name']
                         ],
                         'total'
                     ]
                 ])
                 ->assertJsonPath('data.total', 3)
                 ->assertJsonCount(3, 'data.donors');
    }

    /**
     * Test: POST /api/v1/donors
     * Debe crear un nuevo donante con datos válidos
     */
    public function test_can_create_donor(): void
    {
        $data = ['name' => 'Nuevo Donante'];

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertCreated()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Donor created successfully',
                 ])
                 ->assertJsonStructure([
                     'data' => ['id', 'name']
                 ]);

        $this->assertDatabaseHas('donor', [
            'name' => 'Nuevo Donante'
        ]);
    }

    /**
     * Test: POST /api/v1/donors
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
     * Test: POST /api/v1/donors
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
     * Test: POST /api/v1/donors
     * Debe fallar si el name ya existe
     */
    public function test_name_must_be_unique(): void
    {
        Donor::factory()->create(['name' => 'Donante Existente']);

        $data = ['name' => 'Donante Existente'];
        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name'])
                 ->assertJsonPath('errors.name.0', self::ERROR_UNIQUE);
    }

    /**
     * Test: POST /api/v1/donors
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
     * Test: GET /api/v1/donors/{id}
     * Debe mostrar un donante específico
     */
    public function test_can_show_donor(): void
    {
        $donor = Donor::factory()->create();

        $response = $this->getJson(self::BASE_URL . "/{$donor->id}", $this->authHeaders());

        $response->assertOk()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Donor found',
                     'data' => [
                         'id' => $donor->id,
                         'name' => $donor->name
                     ]
                 ]);
    }

    /**
     * Test: PUT /api/v1/donors/{id}
     * Debe actualizar un donante existente
     */
    public function test_can_update_donor(): void
    {
        $donor = Donor::factory()->create(['name' => 'Nombre Original']);
        $data = ['name' => 'Nombre Actualizado'];

        $response = $this->putJson(self::BASE_URL . "/{$donor->id}", $data, $this->authHeaders());

        $response->assertOk()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Donor uploaded successfully',
                 ]);

        $this->assertDatabaseHas('donor', [
            'id' => $donor->id,
            'name' => 'Nombre Actualizado'
        ]);

        $this->assertDatabaseMissing('donor', [
            'id' => $donor->id,
            'name' => 'Nombre Original'
        ]);
    }

    /**
     * Test: PUT /api/v1/donors/{id}
     * Debe fallar si se intenta actualizar con un nombre duplicado
     */
    public function test_cannot_update_with_duplicate_name(): void
    {
        $donor1 = Donor::factory()->create(['name' => 'Donante Norte']);
        $donor2 = Donor::factory()->create(['name' => 'Donante Sur']);

        $data = ['name' => 'Donante Norte'];
        $response = $this->putJson(self::BASE_URL . "/{$donor2->id}", $data, $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name'])
                 ->assertJsonPath('errors.name.0', self::ERROR_UNIQUE);
    }

    /**
     * Test: POST /api/v1/donors
     * Debe hacer trim de espacios en blanco
     */
    public function test_trims_whitespace_from_name(): void
    {
        $data = ['name' => '  Donante   con Espacios  '];

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertCreated();
        $this->assertDatabaseHas('donor', [
            'name' => 'Donante con Espacios'
        ]);
    }

    /**
     * Test: POST /api/v1/donors
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
     * Test: PUT /api/v1/donors/{id}
     * Debe permitir actualizar con el mismo nombre
     */
    public function test_can_update_donor_with_same_name(): void
    {
        $donor = Donor::factory()->create(['name' => '    Mismo Nombre']);

        $response = $this->putJson(
            self::BASE_URL . "/{$donor->id}",
            ['name' => 'Mismo Nombre'],
            $this->authHeaders()
        );

        $response->assertOk();
        $this->assertDatabaseHas('donor', [
            'id' => $donor->id,
            'name' => 'Mismo Nombre'
        ]);
    }

    /**
     * Test: POST /api/v1/donors
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
     * Test: GET /api/v1/donors
     * Debe retornar lista vacía cuando no hay donantes
     */
    public function test_list_returns_empty_when_no_donors(): void
    {
        $response = $this->getJson(self::BASE_URL, $this->authHeaders());

        $response->assertOk()
                 ->assertJsonPath('data.total', 0)
                 ->assertJsonCount(0, 'data.donors');
    }

    /**
     * Test: GET /api/v1/donors/search
     * Debe buscar un donante por nombre exacto
     */
    public function test_can_search_donor_by_name(): void
    {
        $donor = Donor::factory()->create(['name' => 'Donante Buscable']);

        $response = $this->getJson(self::BASE_URL . '/search?name=Donante Buscable', $this->authHeaders());

        $response->assertOk()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Donor found',
                     'data' => [
                         'id' => $donor->id,
                         'name' => $donor->name
                     ]
                 ]);
    }

    /**
     * Test: GET /api/v1/donors/search
     * Debe buscar sin distinción de mayúsculas/minúsculas
     */
    public function test_search_is_case_insensitive(): void
    {
        $donor = Donor::factory()->create(['name' => 'Donante Especial']);

        $response = $this->getJson(self::BASE_URL . '/search?name=donante especial', $this->authHeaders());

        $response->assertOk()
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'id' => $donor->id,
                         'name' => 'Donante Especial'
                     ]
                 ]);
    }

    /**
     * Test: GET /api/v1/donors/search
     * Debe retornar 404 cuando no encuentra donante por nombre
     */
    public function test_search_returns_404_when_donor_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/search?name=No Existe', $this->authHeaders());

        $response->assertNotFound()
                 ->assertJson(['success' => false]);
    }

    /**
     * Test: GET /api/v1/donors/search
     * Debe fallar si no se proporciona parámetro name
     */
    public function test_search_requires_name_parameter(): void
    {
        $response = $this->getJson(self::BASE_URL . '/search', $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    public function test_can_delete_donor_without_relations(): void
    {
        $donor = Donor::factory()->create();

        $response = $this->deleteJson(self::BASE_URL . "/{$donor->id}", [], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('message', 'Donor deleted successfully');

        $this->assertDatabaseMissing('donor', ['id' => $donor->id]);
    }

    public function test_cannot_delete_donor_with_project_relations(): void
    {
        $donor = Donor::factory()->create();
        $program = Program::factory()->create();
        $project = Project::factory()->create(['program_id' => $program->id]);

        DB::table('project_donor')->insert([
            'project_id' => $project->id,
            'donor_id' => $donor->id,
            'contribution' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->deleteJson(self::BASE_URL . "/{$donor->id}", [], $this->authHeaders());

        $response->assertStatus(409)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('donor', ['id' => $donor->id]);
    }

    public function test_delete_returns_not_found_for_non_existent_donor(): void
    {
        $response = $this->deleteJson(self::BASE_URL . '/999999', [], $this->authHeaders());

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Donor not Found');
    }
}
