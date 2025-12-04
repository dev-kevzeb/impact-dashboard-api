<?php

namespace Tests\Feature;

use App\Modules\Agency\Domain\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgencyTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/agencies';

    public function test_can_list_agencies(): void
    {
        Agency::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'agencies' => [
                        '*' => ['id', 'name', 'url', 'is_approved']
                    ],
                    'total'
                ]
            ])
            ->assertJsonPath('data.total', 3)
            ->assertJsonCount(3, 'data.agencies');
    }

    public function test_list_returns_empty_when_no_agencies(): void
    {
        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
            ->assertJsonPath('data.total', 0)
            ->assertJsonCount(0, 'data.agencies');
    }

    public function test_can_create_agency(): void
    {
        $data = [
            'name' => 'Agencia Cooperante',
            'url' => 'https://example.org',
            'is_approved' => true
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertCreated()
            ->assertJsonPath('message', 'Agency successfully created')
            ->assertJsonStructure([
                'data' => ['id', 'name', 'url', 'is_approved']
            ]);

        $this->assertDatabaseHas('agency', [
            'name' => 'Agencia Cooperante',
            'url' => 'https://example.org',
            'is_approved' => 1
        ]);
    }

    public function test_validation_errors_on_create(): void
    {
        $response = $this->postJson(self::BASE_URL, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'url', 'is_approved']);
    }

    public function test_domain_errors_on_create(): void
    {
        $data = [
            'name' => 'Agencia',
            'url' => 'invalid-url',
            'is_approved' => true
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
             ->assertJsonValidationErrors(['url']);
    }

    public function test_can_show_agency(): void
    {
        $agency = Agency::factory()->create();

        $response = $this->getJson(self::BASE_URL . "/{$agency->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Agencia encontrada',
                'data' => [
                    'id' => $agency->id,
                    'name' => $agency->name,
                    'url' => $agency->url,
                    'is_approved' => (bool)$agency->is_approved,
                ]
            ]);
    }

    public function test_can_update_agency(): void
    {
        $agency = Agency::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$agency->id}", [
            'name' => 'Agencia Actualizada',
            'url' => 'https://updated.org',
            'is_approved' => false
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Agency successfully updated');

        $this->assertDatabaseHas('agency', [
            'id' => $agency->id,
            'name' => 'Agencia Actualizada',
            'url' => 'https://updated.org',
            'is_approved' => 0
        ]);
    }

    public function test_validation_errors_on_update(): void
    {
        $agency = Agency::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$agency->id}", [
            'name' => '',
            'url' => '',
            'is_approved' => null
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'url', 'is_approved']);
    }

    public function test_domain_error_on_update(): void
    {
        $agency = Agency::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$agency->id}", [
            'name' => 'Valid Name',
            'url' => 'notaurl',
            'is_approved' => true
        ]);

        $response->assertStatus(422)
             ->assertJsonValidationErrors(['url']);
    }

    public function test_can_search_agency_by_name(): void
    {
        $agency = Agency::factory()->create(['name' => 'Agencia Boliviana']);

        $response = $this->getJson(self::BASE_URL . '/search?name=boliviana');

        $response->assertOk()
            ->assertJsonPath('data.name', 'Agencia Boliviana');
    }

    public function test_search_returns_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/search?name=xyz');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Agencia no encontrado');
    }
}
