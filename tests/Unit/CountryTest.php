<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Kpa;
use Exception;
use RuntimeException;

class CountryTest extends TestCase
{
    private Currency $validCurrency;
    private array $validKpas;
    protected function setUp(): void
    {
        parent::setUp();
        
        // Preparar Currency válida para todos los tests
        $this->validCurrency = Currency::at("USD");
        $kpa = new Kpa("Nombre KPA", 50, ["Output1", "Output2"]);
        $kpa2 = new Kpa("Nombre KPA 2", 50, ["Output1", "Output2"]);
        $kpa3 = new Kpa("Nombre KPA 3", 50, ["Output1", "Output2"]);
        $this->validKpas = [$kpa, $kpa2, $kpa3];
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
        $country = Country::at("Bolivia", $this->validCurrency, $this->validKpas);

        $this->assertEquals("Bolivia", $country->getName());
        $this->assertEquals($this->validCurrency, $country->getCurrency());
        $this->assertInstanceOf(Country::class, $country);
    }



    public function test_country_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Country::at("", $this->validCurrency, $this->validKpas);
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
                Country::at("   ", $this->validCurrency, $this->validKpas);
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
                Country::at("A", $this->validCurrency, $this->validKpas);
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
                Country::at($longName, $this->validCurrency, $this->validKpas);
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
                Country::at("Bolivia123", $this->validCurrency, $this->validKpas);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Country::$ERROR_NAME_INVALID_CHARACTERS, $exception->getMessage());
            }
        );

        $this->shouldThrowAndAssert(
            function () {
                Country::at("Bolivia@#$", $this->validCurrency, $this->validKpas);
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
                Country::at("Bolivia", null, $this->validKpas);
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
                Country::at("Bolivia", "USD", $this->validKpas); // String en lugar de Currency
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
            $country = Country::at($countryName, $currency, $this->validKpas);

            $this->assertEquals($countryName, $country->getName());
            $this->assertEquals($currency, $country->getCurrency());
        }
    }

    public function test_country_name_gets_trimmed()
    {
        $country = Country::at("  Bolivia  ", $this->validCurrency, $this->validKpas);
        
        $this->assertEquals("Bolivia", $country->getName()); // Sin espacios
    }

    public function test_country_supports_unicode_characters()
    {
        $currency = Currency::at("EUR");
        $country = Country::at("España", $currency, $this->validKpas);
        
        $this->assertEquals("España", $country->getName());
    }

    // <summary>Verifica que pasar los KPAs como un valor no-array lance una excepción.</summary>
    public function test_kpas_must_be_array()
    {
        $this->shouldThrowAndAssert(
            function () {
                Country::at("Bolivia", $this->validCurrency, "not-an-array");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Country::$ERROR_KPA_MUST_BE_ARRAY, $exception->getMessage());
            }
        );
    }

    // <summary>Verifica que pasar un array vacío de KPAs lance una excepción indicando que debe haber al menos uno.</summary>
    public function test_kpas_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Country::at("Bolivia", $this->validCurrency, []);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals('debe haber al menos un KPA en el array de KPAs', $exception->getMessage());
            }
        );
    }

    // <summary>Verifica que cada elemento del array de KPAs sea una instancia de Kpa.</summary>
    public function test_each_kpa_must_be_instance_of_kpa()
    {
        $this->shouldThrowAndAssert(
            function () {
                Country::at("Bolivia", $this->validCurrency, ["not-kpa", new Kpa("Valido", 10, [])]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals('cada KPA debe ser una instancia de Kpa', $exception->getMessage());
            }
        );
    }

    // <summary>Verifica que getKpas devuelva el array de Kpa provisto al crear el Country.</summary>
    public function test_get_kpas_returns_array_of_kpa()
    {
        $country = Country::at("Bolivia", $this->validCurrency, $this->validKpas);
        $kpas = $country->getKpas();

        $this->assertIsArray($kpas);
        $this->assertCount(3, $kpas);
        foreach ($kpas as $item) {
            $this->assertInstanceOf(Kpa::class, $item);
        }
    }
}
