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
        $kpa = Kpa::at("Desarrollo Rural", 50, ["Output1", "Output2"]);

        $this->assertInstanceOf(Kpa::class, $kpa);
        $this->assertEquals("Desarrollo Rural", $kpa->getName());
    }

    public function test_kpa_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Kpa::at("", 50, ["Output1", "Output2"]);
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
                Kpa::at("   ", 50, ["Output1", "Output2"]);
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
                Kpa::at("A", 50, ["Output1", "Output2"]);
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
                Kpa::at($longName, 50, ["Output1", "Output2"]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Kpa::$ERROR_NAME_MAX_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_kpa_name_with_minimum_length_is_valid()
    {
        $kpa = Kpa::at("AI", 50, ["Output1", "Output2"]);
        
        $this->assertInstanceOf(Kpa::class, $kpa);
        $this->assertEquals("AI", $kpa->getName());
    }

    public function test_kpa_name_with_maximum_length_is_valid()
    {
        $maxName = str_repeat("A", 100); // 100 caracteres exactos
        $kpa = Kpa::at($maxName, 50, ["Output1", "Output2"]);
        
        $this->assertInstanceOf(Kpa::class, $kpa);
        $this->assertEquals($maxName, $kpa->getName());
    }

    public function test_kpa_name_with_special_characters_is_valid()
    {
        $kpa = Kpa::at("Desarrollo Rural & Sostenible" , 50, ["Output1", "Output2"]);
        
        $this->assertInstanceOf(Kpa::class, $kpa);
        $this->assertEquals("Desarrollo Rural & Sostenible", $kpa->getName());
    }

    public function test_kpa_name_with_unicode_characters_is_valid()
    {
        $kpa = Kpa::at("Educación y Nutrición", 50, ["Output1", "Output2"]);
        
        $this->assertInstanceOf(Kpa::class, $kpa);
        $this->assertEquals("Educación y Nutrición", $kpa->getName());
    }

    public function test_kpa_name_gets_trimmed()
    {
        $kpa = Kpa::at("  Desarrollo Rural  ", 50, ["Output1", "Output2"]);
        
        $this->assertEquals("Desarrollo Rural", $kpa->getName());
    }
    public function test_kpa_implementation_cannot_be_negative(){
        $this->shouldThrowAndAssert(
            function () { Kpa::at("Desarrollo Rural", -10, ["Output1", "Output2"]); },
            RuntimeException::class,
            function ($exception) { $this->assertEquals(Kpa::$IMPLEMENTATION_MUST_BE_BETWEEN_0_AND_100, $exception->getMessage()); }
        );
    }
    public function test_kpa_implementation_cannot_exceed_100(){
        $this->shouldThrowAndAssert(
            function () { Kpa::at("Desarrollo Rural", 150, ["Output1", "Output2"]); },
            RuntimeException::class,
            function ($exception) { $this->assertEquals(Kpa::$IMPLEMENTATION_MUST_BE_BETWEEN_0_AND_100, $exception->getMessage()); }
        );
    }
    public function test_kpa_implementation_must_be_numeric(){
        $this->shouldThrowAndAssert(
            function () { Kpa::at("Desarrollo Rural", "Hola", ["Output1", "Output2"]); },
            RuntimeException::class,
            function ($exception) { $this->assertEquals(Kpa::$IMPLEMENTATION_MUST_BE_NUMERIC, $exception->getMessage()); }
        );  
    }
    public function test_kpa_strategic_outputs_must_be_array(){
        $this->shouldThrowAndAssert(
            function () { Kpa::at("Desarrollo Rural", 50, "todo menos un array"); },
            RuntimeException::class,
            function ($exception) { $this->assertEquals(Kpa::$STRATEGICOUTPUTS_MUST_BE_ARRAY, $exception->getMessage()); }
        );  
    }
    public function test_kpa_can_be_created_with_empty_strategic_outputs_array(){
        $kpa = Kpa::at("Desarrollo Rural", 50, []);
        $this->assertInstanceOf(Kpa::class, $kpa);
        $this->assertEquals("Desarrollo Rural", $kpa->getName());
    }  

}