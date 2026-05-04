<?php

namespace Tests\Feature;

use App\Modules\Kpa\Domain\Kpa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpaTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/kpas';

    private const ERROR_NAME_EMPTY = 'The KPA name must not be empty';
    private const ERROR_NAME_MIN_LENGTH = 'The KPA name must be at least 2 characters long';
    private const ERROR_NAME_MAX_LENGTH = 'The KPA name must not exceed 100 characters';

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
                        '*' => ['id', 'name', 'strategic_outputs_count']
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
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertCreated()
                ->assertJson([
                    'success' => true,
                    'message' => 'KPA created successfully'
                ])
                ->assertJsonStructure([
                    'data' => ['id', 'name', 'strategic_outputs_count']
                ]);

        $this->assertDatabaseHas('kpa', [
            'name' => 'Kpa Nuevo',
        ]);
    }

    /** NAME REQUIRED */
    public function test_name_is_required(): void
    {
        $response = $this->postJson(self::BASE_URL, []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name'])
                ->assertJsonPath('errors.name.0', self::ERROR_NAME_EMPTY);
    }

    /** NAME MIN LENGTH */
    public function test_name_must_be_at_least_two_characters(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'A',
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
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name'])
                ->assertJsonPath('errors.name.0', self::ERROR_NAME_MAX_LENGTH);
    }

    /** TRIM NAME */
    public function test_trims_whitespace_from_name(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => '   Kpa Limpio   ',
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
                    'message' => 'KPA found',
                    'data' => [
                        'id' => $kpa->id,
                        'name' => $kpa->name,
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
        $kpa = Kpa::factory()->create(['name' => 'Viejo']);

        $response = $this->putJson(self::BASE_URL . "/{$kpa->id}", [
            'name' => 'Nuevo',
        ]);

        $response->assertOk()
                ->assertJson([
                    'success' => true,
                    'message' => 'KPA uploaded successfully'
                ]);

        $this->assertDatabaseHas('kpa', [
            'id' => $kpa->id,
            'name' => 'Nuevo',
        ]);
    }

    /** UPDATE - TRIM */
    public function test_can_update_with_trimmed_name(): void
    {
        $kpa = Kpa::factory()->create(['name' => 'Original']);

        $response = $this->putJson(self::BASE_URL . "/{$kpa->id}", [
            'name' => '   Kpa Limpio   ',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('kpa', [
            'id' => $kpa->id,
            'name' => 'Kpa Limpio'
        ]);
    }

    public function test_name_must_be_unique(): void
    {
        Kpa::factory()->create(['name' => 'KPA Existente']);

        $response = $this->postJson(self::BASE_URL, ['name' => 'KPA Existente']);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name'])
            ->assertJsonPath('errors.name.0', 'A KPA with this name already exists');
    }
}
