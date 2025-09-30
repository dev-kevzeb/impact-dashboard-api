<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Kpa;
use Exception;
use RuntimeException;

class KpaTest extends TestCase
{
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

    public function test_kpa_can_be_created_with_valid_name()
    {
        $kpa = Kpa::at("Desarrollo Rural");

        $this->assertInstanceOf(Kpa::class, $kpa);
        $this->assertEquals("Desarrollo Rural", $kpa->getName());
    }

    public function test_kpa_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Kpa::at("");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Kpa::$ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_kpa_name_cannot_be_only_spaces()
    {
        $this->shouldThrowAndAssert(
            function () {
                Kpa::at("   ");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Kpa::$ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_kpa_name_too_short_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                Kpa::at("A");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Kpa::$ERROR_NAME_MIN_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_kpa_name_too_long_throws_runtime_exception()
    {
        $longName = str_repeat("A", 101); // 101 caracteres

        $this->shouldThrowAndAssert(
            function () use ($longName) {
                Kpa::at($longName);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Kpa::$ERROR_NAME_MAX_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_kpa_name_with_minimum_length_is_valid()
    {
        $kpa = Kpa::at("AI");
        
        $this->assertInstanceOf(Kpa::class, $kpa);
        $this->assertEquals("AI", $kpa->getName());
    }

    public function test_kpa_name_with_maximum_length_is_valid()
    {
        $maxName = str_repeat("A", 100); // 100 caracteres exactos
        $kpa = Kpa::at($maxName);
        
        $this->assertInstanceOf(Kpa::class, $kpa);
        $this->assertEquals($maxName, $kpa->getName());
    }

    public function test_kpa_name_with_special_characters_is_valid()
    {
        $kpa = Kpa::at("Desarrollo Rural & Sostenible");
        
        $this->assertInstanceOf(Kpa::class, $kpa);
        $this->assertEquals("Desarrollo Rural & Sostenible", $kpa->getName());
    }

    public function test_kpa_name_with_unicode_characters_is_valid()
    {
        $kpa = Kpa::at("Educación y Nutrición");
        
        $this->assertInstanceOf(Kpa::class, $kpa);
        $this->assertEquals("Educación y Nutrición", $kpa->getName());
    }

    public function test_kpa_name_gets_trimmed()
    {
        $kpa = Kpa::at("  Desarrollo Rural  ");
        
        $this->assertEquals("Desarrollo Rural", $kpa->getName());
    }
}