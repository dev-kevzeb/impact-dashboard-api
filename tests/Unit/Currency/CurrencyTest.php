<?php

namespace Tests\Unit\Currency;

use Tests\TestCase;
use App\Modules\Currency\Domain\Currency;
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
                $this->assertEquals(Currency::$ERROR_CODE_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_currency_code_cannot_be_only_spaces()
    {
        $this->shouldThrowAndAssert(
            function () {
                Currency::at("   ");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Currency::$ERROR_CODE_EMPTY, $exception->getMessage());
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
                $this->assertEquals(Currency::$ERROR_CODE_LENGTH, $exception->getMessage());
            }
        );

        // Código muy largo
        $this->shouldThrowAndAssert(
            function () {
                Currency::at("USDD");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Currency::$ERROR_CODE_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_currency_code_must_be_uppercase_letters_only()
    {
        // Código con minúsculas se convierte automáticamente a mayúsculas
        $currency = Currency::at("usd");
        $this->assertEquals("USD", $currency->getCode());

        // Código con números
        $this->shouldThrowAndAssert(
            function () {
                Currency::at("U5D");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Currency::$ERROR_CODE_FORMAT, $exception->getMessage());
            }
        );

        // Código con caracteres especiales
        $this->shouldThrowAndAssert(
            function () {
                Currency::at("U@D");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Currency::$ERROR_CODE_FORMAT, $exception->getMessage());
            }
        );
    }

    public function test_currency_with_valid_iso_codes()
    {
        $validCodes = ['USD', 'EUR', 'BOB', 'BRL', 'ARS', 'PEN', 'CLP', 'COP', 'GBP', 'JPY', 'UYU', 'PYG', 'CAD', 'CHF'];
        foreach ($validCodes as $code) {
            $currency = Currency::at($code);
            $this->assertEquals($code, $currency->getCode());
            $this->assertInstanceOf(Currency::class, $currency);
        }
    }

    public function test_currency_code_with_spaces_gets_trimmed()
    {
        $currency = Currency::at(" USD ");
        
        $this->assertEquals("USD", $currency->getCode());
        $this->assertInstanceOf(Currency::class, $currency);
    }

}