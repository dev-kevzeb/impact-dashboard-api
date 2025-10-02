<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Currency;
use Exception;
use RuntimeException;

class CurrencyTest extends TestCase
{
    // Closure para manejo de errores
    public function shouldThrowAndAssert($should, $exceptionType, $assertions)
    {
        try {
            $should->__invoke();
            $this->fail();
        } catch (Exception $exception) {
            $this->assertEquals($exceptionType, get_class($exception));
            $assertions->__invoke($exception);
        }
    }

    public function test_currency_can_be_created_with_valid_code()
    {
        $currency = Currency::at("USD");

        $this->assertEquals("USD", $currency->getCode());
        $this->assertInstanceOf(Currency::class, $currency);
    }

    public function test_currency_code_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Currency::at("");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el código de moneda no debe ir vacio", $exception->getMessage());
            }
        );
    }



    public function test_currency_code_must_be_exactly_three_characters()
    {
        // Código muy corto
        $this->shouldThrowAndAssert(
            function () {
                Currency::at("US");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el código de moneda debe tener exactamente 3 caracteres", $exception->getMessage());
            }
        );

        // Código muy largo
        $this->shouldThrowAndAssert(
            function () {
                Currency::at("USDD");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el código de moneda debe tener exactamente 3 caracteres", $exception->getMessage());
            }
        );
    }

    public function test_currency_code_must_be_uppercase_letters_only()
    {
        // Código con minúsculas
        $this->shouldThrowAndAssert(
            function () {
                Currency::at("usd");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el código de moneda debe contener solo letras mayúsculas", $exception->getMessage());
            }
        );

        // Código con números
        $this->shouldThrowAndAssert(
            function () {
                Currency::at("U5D");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el código de moneda debe contener solo letras mayúsculas", $exception->getMessage());
            }
        );

        // Código con caracteres especiales
        $this->shouldThrowAndAssert(
            function () {
                Currency::at("U@D"); // Usar @ en lugar de $ para evitar problemas de shell
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el código de moneda debe contener solo letras mayúsculas", $exception->getMessage());
            }
        );
    }

    public function test_currency_code_must_be_valid_iso_code()
    {
        // Código no válido según ISO 4217
        $this->shouldThrowAndAssert(
            function () {
                Currency::at("XXX");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el código de moneda debe ser un código ISO 4217 válido", $exception->getMessage());
            }
        );

        $this->shouldThrowAndAssert(
            function () {
                Currency::at("ABC");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el código de moneda debe ser un código ISO 4217 válido", $exception->getMessage());
            }
        );
    }

    public function test_currency_with_valid_iso_codes()
    {
        $validCodes = ['USD', 'EUR', 'BOB', 'BRL', 'ARS', 'PEN', 'CLP', 'COP', 'GBP', 'JPY'];
        
        foreach ($validCodes as $code) {
            $currency = Currency::at($code);
            $this->assertEquals($code, $currency->getCode());
            $this->assertInstanceOf(Currency::class, $currency);
        }
    }

    public function test_currency_code_with_spaces_gets_trimmed()
    {
        // Los espacios se deberían eliminar automáticamente
        $currency = Currency::at(" USD ");
        
        $this->assertEquals("USD", $currency->getCode());
        $this->assertInstanceOf(Currency::class, $currency);
    }

    public function test_currency_supports_common_latin_american_currencies()
    {
        $latinAmericanCodes = ['BOB', 'BRL', 'ARS', 'PEN', 'CLP', 'COP', 'UYU', 'PYG'];
        
        foreach ($latinAmericanCodes as $code) {
            $currency = Currency::at($code);
            $this->assertEquals($code, $currency->getCode());
        }
    }
}