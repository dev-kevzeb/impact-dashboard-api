<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Country;
use App\Models\Currency;
use Exception;
use RuntimeException;

class CountryTest extends TestCase
{
    private Currency $validCurrency;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Preparar Currency válida para todos los tests
        $this->validCurrency = Currency::at("USD");
    }

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

    public function test_country_can_be_created_with_valid_data()
    {
        $country = Country::at("Bolivia", $this->validCurrency);

        $this->assertEquals("Bolivia", $country->getName());
        $this->assertEquals($this->validCurrency, $country->getCurrency());
        $this->assertInstanceOf(Country::class, $country);
    }



    public function test_country_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Country::at("", $this->validCurrency);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Country::$ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_country_name_cannot_be_only_spaces()
    {
        $this->shouldThrowAndAssert(
            function () {
                Country::at("   ", $this->validCurrency);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Country::$ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_country_name_must_have_minimum_length()
    {
        $this->shouldThrowAndAssert(
            function () {
                Country::at("A", $this->validCurrency);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Country::$ERROR_NAME_TOO_SHORT, $exception->getMessage());
            }
        );
    }

    public function test_country_name_must_not_exceed_maximum_length()
    {
        $longName = str_repeat("A", 101); // 101 caracteres
        
        $this->shouldThrowAndAssert(
            function () use ($longName) {
                Country::at($longName, $this->validCurrency);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Country::$ERROR_NAME_TOO_LONG, $exception->getMessage());
            }
        );
    }

    public function test_country_name_must_contain_valid_characters()
    {
        // Solo letras, espacios, guiones y apostrofes son válidos
        $this->shouldThrowAndAssert(
            function () {
                Country::at("Bolivia123", $this->validCurrency);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Country::$ERROR_NAME_INVALID_CHARACTERS, $exception->getMessage());
            }
        );

        $this->shouldThrowAndAssert(
            function () {
                Country::at("Bolivia@#$", $this->validCurrency);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Country::$ERROR_NAME_INVALID_CHARACTERS, $exception->getMessage());
            }
        );
    }

    public function test_country_currency_cannot_be_null()
    {
        $this->shouldThrowAndAssert(
            function () {
                Country::at("Bolivia", null);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Country::$ERROR_CURRENCY_INVALID, $exception->getMessage());
            }
        );
    }

    public function test_country_currency_must_be_currency_instance()
    {
        $this->shouldThrowAndAssert(
            function () {
                Country::at("Bolivia", "USD"); // String en lugar de Currency
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Country::$ERROR_CURRENCY_INVALID, $exception->getMessage());
            }
        );
    }

    public function test_country_with_valid_names_and_currencies()
    {
        $testCases = [
            ['Bolivia', 'BOB'],
            ['Estados Unidos', 'USD'],
            ['Reino Unido', 'GBP'],
            ['Côte d\'Ivoire', 'XOF'], // Con apostrofe
            ['Guinea-Bissau', 'XOF'],  // Con guión
        ];

        foreach ($testCases as [$countryName, $currencyCode]) {
            $currency = Currency::at($currencyCode);
            $country = Country::at($countryName, $currency);
            
            $this->assertEquals($countryName, $country->getName());
            $this->assertEquals($currency, $country->getCurrency());
        }
    }

    public function test_country_name_gets_trimmed()
    {
        $country = Country::at("  Bolivia  ", $this->validCurrency);
        
        $this->assertEquals("Bolivia", $country->getName()); // Sin espacios
    }

    public function test_country_supports_unicode_characters()
    {
        $currency = Currency::at("EUR");
        $country = Country::at("España", $currency);
        
        $this->assertEquals("España", $country->getName());
    }
}
