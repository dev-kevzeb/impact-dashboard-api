<?php

namespace Tests\Feature;

use App\Modules\Currency\Domain\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/currencies';

    private const ERROR_CODE_EMPTY  = 'The currency code must not be empty';
    private const ERROR_CODE_LENGTH = 'The currency code must be exactly 3 characters';
    private const ERROR_CODE_FORMAT = 'The currency code must contain only uppercase letters (no numbers or symbols)';
    private const ERROR_CODE_UNIQUE = 'This currency already exists in the system';

    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->headers = $this->authHeaders('admin');
    }

    /** LISTAR */
    public function test_can_list_currencies(): void
    {
        Currency::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL, $this->headers);

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
        $response = $this->getJson(self::BASE_URL, $this->headers);

        $response->assertOk()
                 ->assertJsonPath('data.total', 0)
                 ->assertJsonCount(0, 'data.currencies');
    }

    /** CREAR CORRECTAMENTE */
    public function test_can_create_currency(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'code' => 'USD'
        ], $this->headers);

        $response->assertCreated()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Currency created successfully',
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
        $response = $this->postJson(self::BASE_URL, [], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code'])
                 ->assertJsonPath('errors.code.0', self::ERROR_CODE_EMPTY);
    }

    /** ERROR - LONGITUD EXACTA 3 */
    public function test_code_must_have_exactly_three_characters(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'code' => 'US'
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code'])
                 ->assertJsonPath('errors.code.0', self::ERROR_CODE_LENGTH);
    }

    public function test_code_must_have_exactly_three_characters_not_more(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'code' => 'USDDDD'
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code'])
                 ->assertJsonPath('errors.code.0', self::ERROR_CODE_LENGTH);
    }

    /** ERROR - SOLO LETRAS */
    public function test_code_must_contain_only_letters(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'code' => 'U5D'
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code'])
                 ->assertJsonPath('errors.code.0', self::ERROR_CODE_FORMAT);
    }

    /** CREAR - CÓDIGO PERSONALIZADO */
    public function test_can_create_custom_currency_not_iso(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'code' => 'XYZ'
        ], $this->headers);

        $response->assertCreated()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Currency created successfully',
                 ])
                 ->assertJsonStructure([
                     'data' => ['id', 'code']
                 ]);

        $this->assertDatabaseHas('currency', [
            'code' => 'XYZ'
        ]);
    }

    /** ERROR - UNICIDAD */
    public function test_code_must_be_unique(): void
    {
        Currency::factory()->create(['code' => 'USD']);

        $response = $this->postJson(self::BASE_URL, ['code' => 'USD'], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code'])
                 ->assertJsonPath('errors.code.0', self::ERROR_CODE_UNIQUE);
    }

    /** CREAR - TRIM */
    public function test_trims_whitespace_from_code(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'code' => '   usd   '
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code'])
                 ->assertJsonPath('errors.code.0', self::ERROR_CODE_FORMAT);

        $this->assertDatabaseMissing('currency', [
            'code' => 'USD',
        ]);
    }

    /** CREAR - SOLO ESPACIOS */
    public function test_code_cannot_be_only_whitespace(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'code' => '   '
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code']);
    }

    /** MOSTRAR */
    public function test_can_show_currency(): void
    {
        $currency = Currency::factory()->create(['code' => 'EUR']);

        $response = $this->getJson(self::BASE_URL . "/{$currency->id}", $this->headers);

        $response->assertOk()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Currency found',
                     'data' => [
                         'id' => $currency->id,
                         'code' => 'EUR'
                     ]
                 ]);
    }

    /** MOSTRAR - NO EXISTE */
    public function test_returns_404_when_currency_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/99999', $this->headers);

        $response->assertNotFound()
                 ->assertJson(['success' => false]);
    }

    /** UPDATE CORRECTAMENTE */
    public function test_can_update_currency(): void
    {
        $currency = Currency::factory()->create(['code' => 'USD']);

        $response = $this->putJson(self::BASE_URL . "/{$currency->id}", [
            'code' => 'EUR'
        ], $this->headers);

        $response->assertOk()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Currency uploaded successfully',
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
        ], $this->headers);

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
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code'])
                 ->assertJsonPath('errors.code.0', self::ERROR_CODE_FORMAT);

        $this->assertDatabaseHas('currency', [
            'id' => $currency->id,
            'code' => 'USD'
        ]);
    }

    /** UPDATE - CÓDIGO PERSONALIZADO */
    public function test_can_update_to_custom_uppercase_code(): void
    {
        $currency = Currency::factory()->create(['code' => 'USD']);

        $response = $this->putJson(self::BASE_URL . "/{$currency->id}", [
            'code' => 'ABC'
        ], $this->headers);

        $response->assertOk()
                 ->assertJson([
                     'success' => true,
                     'message' => 'Currency uploaded successfully',
                 ]);

        $this->assertDatabaseHas('currency', [
            'id' => $currency->id,
            'code' => 'ABC'
        ]);
    }

}
