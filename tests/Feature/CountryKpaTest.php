<?php

namespace Tests\Feature;

use App\Modules\Country\Domain\Country;
use App\Modules\Kpa\Domain\Kpa;
use App\Modules\CountryKpa\Domain\CountryKpa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryKpaTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/country_kpas';

    private const ERROR_COUNTRY_REQUIRED = 'Country is required';
    private const ERROR_KPA_REQUIRED = 'KPA is mandatory';
    private const ERROR_COUNTRY_INVALID = 'the specified country does not exist';
    private const ERROR_KPA_INVALID = 'The specified KPA does not exist';
    private const ERROR_DUPLICATE = 'The Country is already associated with the KPA';


    /** LISTAR TODOS */
    public function test_can_list_all_country_kpa(): void
    {
        CountryKpa::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL, $this->authHeaders());

        $response->assertOk()
                 ->assertJsonPath('data.total', 3)
                 ->assertJsonCount(3, 'data.items');
    }

    /** LISTAR VACÍO */
    public function test_list_returns_empty_when_no_records(): void
    {
        $response = $this->getJson(self::BASE_URL, $this->authHeaders());

        $response->assertOk()
                 ->assertJsonPath('data.total', 0)
                 ->assertJsonCount(0, 'data.items');
    }

    /** LISTAR POR PAÍS */
    public function test_can_list_kpas_by_country(): void
    {
        $country = Country::factory()->create();
        CountryKpa::factory()->count(2)->create(['id_country' => $country->id]);

        $response = $this->getJson(self::BASE_URL . "?country={$country->id}", $this->authHeaders());

        $response->assertOk()
             ->assertJsonPath('data.total', 2)
             ->assertJsonCount(2, 'data.data');
    }

    /** SHOW */
    public function test_can_show_country_kpa(): void
    {
        $record = CountryKpa::factory()->create();

        $response = $this->getJson(self::BASE_URL . "/{$record->id}", $this->authHeaders());

        $response->assertOk()
                 ->assertJsonPath('data.country_id', $record->country_id)
                 ->assertJsonPath('data.kpa_id', $record->kpa_id);
    }

    /** STORE VÁLIDO */
    public function test_can_create_country_kpa(): void
    {
        $country = Country::factory()->create();
        $kpa = Kpa::factory()->create();

        $data = [
            'id_country' => $country->id,
            'id_kpa'     => $kpa->id
        ];

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertCreated()
             ->assertJson(['message' => 'Country-KPA relationship created successfully']);

        $this->assertDatabaseHas('country_kpa', $data);
    }

    /** STORE - PAÍS REQUERIDO */
    public function test_country_is_required(): void
    {
        $kpa = Kpa::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'id_kpa' => $kpa->id
        ], $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['id_country'])
                 ->assertJsonPath('errors.id_country.0', self::ERROR_COUNTRY_REQUIRED);
    }

    /** STORE - KPA REQUERIDO */
    public function test_kpa_is_required(): void
    {
        $country = Country::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'id_country' => $country->id
        ], $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['id_kpa'])
                 ->assertJsonPath('errors.id_kpa.0', self::ERROR_KPA_REQUIRED);
    }

    /** STORE - PAÍS DEBE EXISTIR */
    public function test_country_must_exist(): void
    {
        $kpa = Kpa::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'id_country' => 999,
            'id_kpa' => $kpa->id
        ], $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['id_country'])
                 ->assertJsonPath('errors.id_country.0', self::ERROR_COUNTRY_INVALID);
    }

    /** STORE - KPA DEBE EXISTIR */
    public function test_kpa_must_exist(): void
    {
        $country = Country::factory()->create();

        $response = $this->postJson(self::BASE_URL, [
            'id_country' => $country->id,
            'id_kpa' => 999
        ], $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['id_kpa'])
                 ->assertJsonPath('errors.id_kpa.0', self::ERROR_KPA_INVALID);
    }

    /** STORE - DUPLICADO */
    public function test_country_kpa_must_be_unique(): void
    {
        $country = Country::factory()->create();
        $kpa = Kpa::factory()->create();

        CountryKpa::factory()->create([
            'id_country' => $country->id,
            'id_kpa'     => $kpa->id
        ]);

        $response = $this->postJson(self::BASE_URL, [
            'id_country' => $country->id,
            'id_kpa'     => $kpa->id
        ], $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['id_country'])
                 ->assertJsonPath('errors.id_country.0', self::ERROR_DUPLICATE);
    }

    /** UPDATE VÁLIDO */
    public function test_can_update_country_kpa(): void
    {
        $record = CountryKpa::factory()->create();

        $newCountry = Country::factory()->create();
        $newKpa = Kpa::factory()->create();

        $data = [
            'id_country' => $newCountry->id,
            'id_kpa'     => $newKpa->id
        ];

        $response = $this->putJson(self::BASE_URL . "/{$record->id}", $data, $this->authHeaders());

        $response->assertOk()
             ->assertJson(['message' => 'Country-KPA relationship updated successfully']);

        $this->assertDatabaseHas('country_kpa', array_merge(['id' => $record->id], $data));
    }

    /** UPDATE - DUPLICADO */
    public function test_cannot_update_to_duplicate_relation(): void
    {
        $country = Country::factory()->create();
        $kpa1 = Kpa::factory()->create();
        $kpa2 = Kpa::factory()->create();

        $existing = CountryKpa::factory()->create([
            'id_country' => $country->id,
            'id_kpa' => $kpa1->id
        ]);

        $toUpdate = CountryKpa::factory()->create([
            'id_country' => $country->id,
            'id_kpa' => $kpa2->id
        ]);

        $response = $this->putJson(self::BASE_URL . "/{$toUpdate->id}", [
            'id_country' => $country->id,
            'id_kpa' => $kpa1->id
        ], $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonPath('errors.id_country.0', self::ERROR_DUPLICATE);
    }


}
