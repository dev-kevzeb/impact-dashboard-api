<?php

namespace Tests\Feature;

use App\Modules\Country\Domain\Country;
use App\Modules\Currency\Domain\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/countries';

    private const ERROR_NAME_EMPTY = 'el nombre del país no debe ir vacio';
    private const ERROR_NAME_TOO_SHORT = 'el nombre del país debe tener al menos 2 caracteres';
    private const ERROR_NAME_TOO_LONG = 'el nombre del país no debe exceder 100 caracteres';
    private const ERROR_NAME_INVALID_CHARACTERS = 'el nombre del país contiene caracteres no válidos';
    private const ERROR_NAME_UNIQUE = 'Ya existe un país con ese nombre';

    private const ERROR_CURRENCY_REQUIRED = 'la moneda es obligatoria';
    private const ERROR_CURRENCY_INVALID = 'la moneda debe ser una instancia de Currency';

    /** LISTAR */
    public function test_can_list_countries(): void
    {
        Country::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
                 ->assertJsonPath('data.total', 3)
                 ->assertJsonCount(3, 'data.countries');
    }

    /** LISTAR VACÍO */
    public function test_list_returns_empty_when_no_countries(): void
    {
        $response = $this->getJson(self::BASE_URL);

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
            'currency_id' => $currency->id,
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertCreated()
                 ->assertJson([
                     'success' => true,
                     'message' => 'País creado exitosamente'
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
            'currency_id' => $currency->id
        ]);

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
        ]);

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
        ]);

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
        ]);

        $response->assertStatus(422)
                 ->assertJsonPath('errors.name.0', self::ERROR_NAME_INVALID_CHARACTERS);
    }

    /** NAME TRIM */
    public function test_name_is_trimmed(): void
    {
        $currency = Currency::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'name' => '   República de Bolivia   ',
            'currency_id' => $currency->id
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('country', [
            'name' => 'República De Bolivia'
        ]);
    }

    /** CURRENCY REQUIRED */
    public function test_currency_id_is_required(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Bolivia'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['currency_id'])
                 ->assertJsonPath('errors.currency_id.0', self::ERROR_CURRENCY_REQUIRED);
    }

    /** CURRENCY MUST EXIST */
    public function test_currency_must_exist(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Bolivia',
            'currency_id' => 999
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['currency_id'])
                 ->assertJsonPath('errors.currency_id.0', self::ERROR_CURRENCY_INVALID);
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
        ]);

        $response->assertStatus(422)
                 ->assertJsonPath('errors.name.0', self::ERROR_NAME_UNIQUE);
    }

    /** SHOW */
    public function test_can_show_country(): void
    {
        $country = Country::factory()->create();

        $response = $this->getJson(self::BASE_URL . "/{$country->id}");

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
            'currency_id' => $currency->id
        ]);

        $response->assertOk()
                 ->assertJson(['message' => 'País actualizado exitosamente']);

        $this->assertDatabaseHas('country', [
            'id' => $country->id,
            'name' => 'Nuevo País',
            'currency_id' => $currency->id,
        ]);
    }

    /** UPDATE NAME UNIQUE */
    public function test_cannot_update_with_duplicate_name(): void
    {
        Country::factory()->create(['name' => 'Chile']);
        $c2 = Country::factory()->create(['name' => 'Argentina']);

        $response = $this->putJson(self::BASE_URL . "/{$c2->id}", [
            'name' => 'Chile',
            'currency_id' => $c2->currency_id
        ]);

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
            'currency_id' => $currency->id
        ]);

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

        $response = $this->getJson(self::BASE_URL . "/search?name={$country->name}");

        $response->assertOk()
                 ->assertJsonPath('data.name', $country->name);
    }

    /** SEARCH NAME REQUIRED */
    public function test_search_requires_name(): void
    {
        $response = $this->getJson(self::BASE_URL . "/search");

        $response->assertStatus(422);
    }

    /** SEARCH NOT FOUND */
    public function test_search_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . "/search?name=PaisInexistente");

        $response->assertNotFound();
    }
}
