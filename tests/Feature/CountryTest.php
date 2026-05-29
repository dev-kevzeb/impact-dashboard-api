<?php

namespace Tests\Feature;

use App\Modules\Country\Domain\Country;
use App\Modules\CountryKpa\Domain\CountryKpa;
use App\Modules\CountryUserRole\Domain\CountryUserRole;
use App\Modules\Currency\Domain\Currency;
use App\Modules\Kpa\Domain\Kpa;
use App\Modules\StrategicOutput\Domain\StrategicOutput;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/countries';

    private const ERROR_NAME_EMPTY = 'The Country name is required';
    private const ERROR_NAME_TOO_SHORT = 'The country name must be at least 2 characters';
    private const ERROR_NAME_TOO_LONG = 'The country name must not exceed 100 characters';
    private const ERROR_NAME_INVALID_CHARACTERS = 'The country name contains invalid characters';
    private const ERROR_NAME_UNIQUE = 'A country with that name already exists';
    private const ERROR_CURRENCY_REQUIRED = 'The Currency is required';
    private const ERROR_CURRENCY_DOES_NOT_EXIST = 'The selected currency does not exist';
    private const ERROR_CURRENCY_INVALID = 'la moneda debe ser una instancia de Currency';

    /** LISTAR */
    public function test_can_list_countries(): void
    {
        Country::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL, $this->authHeaders());

        $response->assertOk()
                 ->assertJsonPath('data.total', 3)
                 ->assertJsonCount(3, 'data.countries');
    }

    /** LISTAR VACÍO */
    public function test_list_returns_empty_when_no_countries(): void
    {
        $response = $this->getJson(self::BASE_URL, $this->authHeaders());

        $response->assertOk()
                 ->assertJsonPath('data.total', 0)
                 ->assertJsonCount(0, 'data.countries');
    }

    /** CREAR VÁLIDO */
    public function test_can_create_country(): void
    {
        $currency = Currency::factory()->create();

        $data = [
            'name' => 'Bolivia',
            'active' => false,
            'currency' => [
                'id' => $currency->id,
                'code' => $currency->code,
            ],
        ];

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertCreated()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Country created successfully'
                 ]);

        $this->assertDatabaseHas('country', [
            'name' => 'Bolivia',
            'currency_id' => $currency->id
        ]);
    }

    /** CREAR VÁLIDO CON NUEVA MONEDA */
    public function test_can_create_country_with_new_currency(): void
    {
        $currency = Currency::factory()->create();

        $data = [
            'name' => 'Bolivia',
            'active' => false,
            'currency' => [
                'code' => $currency->code,
            ],
        ];

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertCreated()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Country created successfully'
                 ]);

        $this->assertDatabaseHas('country', [
            'name' => 'Bolivia',
            'currency_id' => $currency->id
        ]);
    }

    /** NAME REQUIRED */
    public function test_name_is_required(): void
    {
        $currency = Currency::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'currency' => [
                'code' => $currency->code,
            ],
        ], $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name'])
                 ->assertJsonPath('errors.name.0', self::ERROR_NAME_EMPTY);
    }

    /** NAME MIN */
    public function test_name_too_short(): void
    {
        $currency = Currency::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'name' => 'A',
            'currency_id' => $currency->id
        ], $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonPath('errors.name.0', self::ERROR_NAME_TOO_SHORT);
    }

    /** NAME MAX */
    public function test_name_too_long(): void
    {
        $currency = Currency::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'name' => str_repeat('A', 101),
            'currency_id' => $currency->id
        ], $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonPath('errors.name.0', self::ERROR_NAME_TOO_LONG);
    }

    /** NAME INVALID CHARACTERS */
    public function test_name_invalid_characters(): void
    {
        $currency = Currency::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'name' => 'País 123 %&/',
            'currency_id' => $currency->id
        ], $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonPath('errors.name.0', self::ERROR_NAME_INVALID_CHARACTERS);
    }

    /** NAME TRIM */
    public function test_name_is_trimmed(): void
    {
        $currency = Currency::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'name' => '   República    de Bolivia   ',
            'active' => false,
            'currency' => [
                'id' => $currency->id,
                'code' => $currency->code,
            ],
        ], $this->authHeaders());

        $response->assertCreated();

        $this->assertDatabaseHas('country', [
            'name' => 'República De Bolivia'
        ]);
    }

    /** CURRENCY REQUIRED */
    public function test_currency_must_exist(): void
    {
        $currency = Currency::factory()->create(['code' => 'USD']);

        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Bolivia',
            'currency' => [
                'id' => 9999,
                'code' => 'USD'
            ],
        ], $this->authHeaders());

        $response->assertStatus(422)
                ->assertJson([
                    'errors' => [
                        'currency.id' => [
                            self::ERROR_CURRENCY_DOES_NOT_EXIST
                        ]
                    ]
                ]);
    }

    /** NAME UNIQUE */
    public function test_name_must_be_unique(): void
    {
        $currency = Currency::factory()->create();

        Country::factory()->create([
            'name' => 'Peru',
            'currency_id' => $currency->id
        ]);

        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Peru',
            'currency_id' => $currency->id
        ], $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonPath('errors.name.0', self::ERROR_NAME_UNIQUE);
    }

    /** SHOW */
    public function test_can_show_country(): void
    {
        $country = Country::factory()->create();

        $response = $this->getJson(self::BASE_URL . "/{$country->id}", $this->authHeaders());

        $response->assertOk()
                 ->assertJsonPath('data.name', $country->name);
    }

    /** UPDATE */
    public function test_can_update_country(): void
    {
        $country = Country::factory()->create();
        $currency = Currency::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$country->id}", [
            'name' => 'Nuevo País',
            'currency' => [
                'id' => $currency->id,
                'code' => $currency->code,
            ],
        ], $this->authHeaders());

        $response->assertOk()
                 ->assertJson(['message' => 'Country uploaded successfully']);

        $this->assertDatabaseHas('country', [
            'id' => $country->id,
            'name' => 'Nuevo País',
            'currency_id' => $currency->id,
        ]);
    }

    public function test_currency_must_exist_when_update(): void
    {
        $country = Country::factory()->create(['name' => 'Chile']);

        $response = $this->putJson(self::BASE_URL. "/{$country->id}", [
            'name' => 'Bolivia',
            'currency' => [
                'id' => 9999,
                'code' => 'USD'
            ],
        ], $this->authHeaders());

        $response->assertStatus(422)
                ->assertJson([
                    'errors' => [
                        'currency.id' => [
                            self::ERROR_CURRENCY_DOES_NOT_EXIST
                        ]
                    ]
                ]);
    }

    public function test_currency_can_be_new_if_dont_have_id(): void
    {
        $country = Country::factory()->create(['name' => 'Chile']);

        $response = $this->putJson(self::BASE_URL. "/{$country->id}", [
            'name' => 'Bolivia',
            'currency' => [
                'code' => 'USD'
            ],
        ], $this->authHeaders());

        $response->assertOk()
                 ->assertJson(['message' => 'Country uploaded successfully']);
    }

    /** UPDATE NAME UNIQUE */
    public function test_cannot_update_with_duplicate_name(): void
    {
        Country::factory()->create(['name' => 'Chile']);
        $c2 = Country::factory()->create(['name' => 'Argentina']);

        $response = $this->putJson(self::BASE_URL . "/{$c2->id}", [
            'name' => 'Chile',
            'currency_id' => $c2->currency_id
        ], $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonPath('errors.name.0', self::ERROR_NAME_UNIQUE);
    }

    /** UPDATE TRIM */
    public function test_update_trims_name(): void
    {
        $country = Country::factory()->create();
        $currency = Currency::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$country->id}", [
            'name' => '   Bolivia   ',
            'currency' => $currency
        ], $this->authHeaders());

        $response->assertOk();

        $this->assertDatabaseHas('country', [
            'id' => $country->id,
            'name' => 'Bolivia'
        ]);
    }

    /** SEARCH */
    public function test_can_search_country_by_name(): void
    {
        $country = Country::factory()->create();

        $response = $this->getJson(self::BASE_URL . "/search?name={$country->name}", $this->authHeaders());

        $response->assertOk()
                 ->assertJsonPath('data.name', $country->name);
    }

    /** SEARCH NAME REQUIRED */
    public function test_search_requires_name(): void
    {
        $response = $this->getJson(self::BASE_URL . "/search", $this->authHeaders());

        $response->assertStatus(422);
    }

    /** SEARCH NOT FOUND */
    public function test_search_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . "/search?name=PaisInexistente", $this->authHeaders());

        $response->assertNotFound();
    }

    public function test_can_delete_country_without_relations(): void
    {
        $country = Country::factory()->create();

        $response = $this->deleteJson(self::BASE_URL . "/{$country->id}", [], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('message', 'Country deleted successfully');

        $this->assertDatabaseMissing('country', ['id' => $country->id]);
    }

    public function test_can_delete_country_with_kpa_relations_without_strategic_outputs(): void
    {
        $country = Country::factory()->create();
        $kpa = Kpa::factory()->create();

        DB::table('country_kpa')->insert([
            'id_country' => $country->id,
            'id_kpa' => $kpa->id,
        ]);

        $response = $this->deleteJson(self::BASE_URL . "/{$country->id}", [], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('message', 'Country deleted successfully');

        $this->assertDatabaseMissing('country', ['id' => $country->id]);
        $this->assertDatabaseMissing('country_kpa', [
            'id_country' => $country->id,
            'id_kpa' => $kpa->id,
        ]);
    }

    public function test_cannot_delete_country_with_kpa_relations_that_have_strategic_outputs(): void
    {
        $country = Country::factory()->create();
        $kpa = Kpa::factory()->create();

        $countryKpa = CountryKpa::factory()->create([
            'id_country' => $country->id,
            'id_kpa' => $kpa->id,
        ]);

        StrategicOutput::factory()->create([
            'id_ck' => $countryKpa->id,
        ]);

        $response = $this->deleteJson(self::BASE_URL . "/{$country->id}", [], $this->authHeaders());

        $response->assertStatus(409)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('country', ['id' => $country->id]);
    }

    public function test_cannot_delete_country_with_user_relations(): void
    {
        $country = Country::factory()->create();

        CountryUserRole::factory()->create([
            'country_id' => $country->id,
        ]);

        $response = $this->deleteJson(self::BASE_URL . "/{$country->id}", [], $this->authHeaders());

        $response->assertStatus(409)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('country', ['id' => $country->id]);
    }

    public function test_delete_returns_not_found_for_non_existent_country(): void
    {
        $response = $this->deleteJson(self::BASE_URL . '/999999', [], $this->authHeaders());

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Country not Found');
    }

    /** ACTIVATE */
    public function test_cannot_update_active_country(): void
    {
        $country = Country::factory()->create(['active' => true]);
        $currency = Currency::factory()->create();

        $response = $this->putJson(self::BASE_URL . "/{$country->id}", [
            'name' => 'Nuevo Nombre',
            'currency' => [
                'id'   => $currency->id,
                'code' => $currency->code,
            ],
        ], $this->authHeaders());

        $response->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    /** ACTIVATE */
    public function test_can_activate_inactive_country(): void
    {
        $country = Country::factory()->create(['active' => false]);

        $response = $this->patchJson(self::BASE_URL . "/{$country->id}/activate", [], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('message', 'Country activated successfully')
            ->assertJsonPath('data.active', true);

        $this->assertDatabaseHas('country', ['id' => $country->id, 'active' => true]);
    }

    public function test_cannot_activate_already_active_country(): void
    {
        $country = Country::factory()->create(['active' => true]);

        $response = $this->patchJson(self::BASE_URL . "/{$country->id}/activate", [], $this->authHeaders());

        $response->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    public function test_activate_returns_not_found_for_non_existent_country(): void
    {
        $response = $this->patchJson(self::BASE_URL . '/999999/activate', [], $this->authHeaders());

        $response->assertStatus(404);
    }

    /** DEACTIVATE */
    public function test_can_deactivate_active_country(): void
    {
        $country = Country::factory()->create(['active' => true]);

        $response = $this->patchJson(self::BASE_URL . "/{$country->id}/deactivate", [], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('message', 'Country deactivated successfully')
            ->assertJsonPath('data.active', false);

        $this->assertDatabaseHas('country', ['id' => $country->id, 'active' => false]);
    }

    public function test_cannot_deactivate_already_inactive_country(): void
    {
        $country = Country::factory()->create(['active' => false]);

        $response = $this->patchJson(self::BASE_URL . "/{$country->id}/deactivate", [], $this->authHeaders());

        $response->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    public function test_deactivate_returns_not_found_for_non_existent_country(): void
    {
        $response = $this->patchJson(self::BASE_URL . '/999999/deactivate', [], $this->authHeaders());

        $response->assertStatus(404);
    }
}
