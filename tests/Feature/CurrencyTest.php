<?php

namespace Tests\Feature;

use App\Modules\Currency\Domain\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/currencies';

    private const ERROR_CODE_EMPTY  = 'el código de moneda no debe ir vacío';
    private const ERROR_CODE_LENGTH = 'el código de moneda debe tener exactamente 3 caracteres';
    private const ERROR_CODE_FORMAT = 'el código de moneda debe contener solo letras (sin números ni símbolos)';
    private const ERROR_CODE_INVALID = 'el código de moneda debe ser un código ISO 4217 válido';
    private const ERROR_CODE_UNIQUE = 'Esta moneda ya existe en el sistema';

    /** LISTAR */
    public function test_can_list_currencies(): void
    {
        Currency::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'currencies' => [
                             '*' => ['id', 'code']
                         ],
                         'total'
                     ]
                 ])
                 ->assertJsonPath('data.total', 3)
                 ->assertJsonCount(3, 'data.currencies');
    }

    /** LISTAR VACÍO */
    public function test_list_returns_empty_when_no_currencies(): void
    {
        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
                 ->assertJsonPath('data.total', 0)
                 ->assertJsonCount(0, 'data.currencies');
    }

    /** CREAR CORRECTAMENTE */
    public function test_can_create_currency(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'code' => 'USD'
        ]);

        $response->assertCreated()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Moneda creada exitosamente',
                 ])
                 ->assertJsonStructure([
                     'data' => ['id', 'code']
                 ]);

        $this->assertDatabaseHas('currency', [
            'code' => 'USD'
        ]);
    }

    /** ERROR - CODE REQUERIDO */
    public function test_code_is_required(): void
    {
        $response = $this->postJson(self::BASE_URL, []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code'])
                 ->assertJsonPath('errors.code.0', self::ERROR_CODE_EMPTY);
    }

    /** ERROR - LONGITUD EXACTA 3 */
    public function test_code_must_have_exactly_three_characters(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'code' => 'US'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code'])
                 ->assertJsonPath('errors.code.0', self::ERROR_CODE_LENGTH);
    }

    public function test_code_must_have_exactly_three_characters_not_more(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'code' => 'USDDDD'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code'])
                 ->assertJsonPath('errors.code.0', self::ERROR_CODE_LENGTH);
    }

    /** ERROR - SOLO LETRAS */
    public function test_code_must_contain_only_letters(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'code' => 'U5D'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code'])
                 ->assertJsonPath('errors.code.0', self::ERROR_CODE_FORMAT);
    }

    /** ERROR - CÓDIGO ISO INVÁLIDO */
    public function test_code_must_be_valid_iso_4217(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'code' => 'XYZ'
        ]);

        $response->assertStatus(400)
                 ->assertJson(['success' => false])
                 ->assertJsonPath('message', self::ERROR_CODE_INVALID);
    }

    /** ERROR - UNICIDAD */
    public function test_code_must_be_unique(): void
    {
        Currency::factory()->create(['code' => 'USD']);

        $response = $this->postJson(self::BASE_URL, ['code' => 'USD']);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code'])
                 ->assertJsonPath('errors.code.0', self::ERROR_CODE_UNIQUE);
    }

    /** CREAR - TRIM */
    public function test_trims_whitespace_from_code(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'code' => '   usd   '
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('currency', [
            'code' => 'USD',
        ]);
    }

    /** CREAR - SOLO ESPACIOS */
    public function test_code_cannot_be_only_whitespace(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'code' => '   '
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code']);
    }

    /** MOSTRAR */
    public function test_can_show_currency(): void
    {
        $currency = Currency::factory()->create(['code' => 'EUR']);

        $response = $this->getJson(self::BASE_URL . "/{$currency->id}");

        $response->assertOk()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Moneda encontrada',
                     'data' => [
                         'id' => $currency->id,
                         'code' => 'EUR'
                     ]
                 ]);
    }

    /** MOSTRAR - NO EXISTE */
    public function test_returns_404_when_currency_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/99999');

        $response->assertNotFound()
                 ->assertJson(['success' => false]);
    }

    /** UPDATE CORRECTAMENTE */
    public function test_can_update_currency(): void
    {
        $currency = Currency::factory()->create(['code' => 'USD']);

        $response = $this->putJson(self::BASE_URL . "/{$currency->id}", [
            'code' => 'EUR'
        ]);

        $response->assertOk()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Moneda actualizada exitosamente',
                 ]);

        $this->assertDatabaseHas('currency', [
            'id' => $currency->id,
            'code' => 'EUR'
        ]);
    }

    /** UPDATE - DUPLICADO */
    public function test_cannot_update_with_duplicate_code(): void
    {
        Currency::factory()->create(['code' => 'USD']);
        $second = Currency::factory()->create(['code' => 'EUR']);

        $response = $this->putJson(self::BASE_URL . "/{$second->id}", [
            'code' => 'USD'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code'])
                 ->assertJsonPath('errors.code.0', self::ERROR_CODE_UNIQUE);
    }

    /** UPDATE - TRIM */
    public function test_can_update_with_trimmed_code(): void
    {
        $currency = Currency::factory()->create(['code' => 'USD']);

        $response = $this->putJson(self::BASE_URL . "/{$currency->id}", [
            'code' => '   eur   '
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('currency', [
            'id' => $currency->id,
            'code' => 'EUR'
        ]);
    }

    /** UPDATE - ISO INVÁLIDO */
    public function test_update_must_be_valid_iso(): void
    {
        $currency = Currency::factory()->create(['code' => 'USD']);

        $response = $this->putJson(self::BASE_URL . "/{$currency->id}", [
            'code' => 'ABC'
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('message', self::ERROR_CODE_INVALID);
    }

}
