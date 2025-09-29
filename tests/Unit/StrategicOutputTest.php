<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Kpa;
use App\Models\StrategicOutput;
use Exception;
use RuntimeException;

class StrategicOutputTest extends TestCase
{
    private Kpa $validKpa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validKpa = Kpa::at("Desarrollo Rural");
    }

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

    public function test_strategic_output_can_be_created_with_valid_data()
    {
        $strategicOutput = StrategicOutput::at("Incrementar productividad agrícola", $this->validKpa);

        $this->assertInstanceOf(StrategicOutput::class, $strategicOutput);
        $this->assertEquals("Incrementar productividad agrícola", $strategicOutput->getName());
        $this->assertEquals($this->validKpa, $strategicOutput->getKpa());
        $this->assertTrue($strategicOutput->validateName());
    }

    public function test_strategic_output_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                StrategicOutput::at("", $this->validKpa);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(StrategicOutput::ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_strategic_output_name_cannot_be_only_spaces()
    {
        $this->shouldThrowAndAssert(
            function () {
                StrategicOutput::at("   ", $this->validKpa);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(StrategicOutput::ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_strategic_output_name_too_short_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                StrategicOutput::at("A", $this->validKpa);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(StrategicOutput::ERROR_NAME_MIN_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_strategic_output_name_too_long_throws_runtime_exception()
    {
        $longName = str_repeat("A", 201); // 201 caracteres

        $this->shouldThrowAndAssert(
            function () use ($longName) {
                StrategicOutput::at($longName, $this->validKpa);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(StrategicOutput::ERROR_NAME_MAX_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_strategic_output_kpa_cannot_be_null()
    {
        $this->shouldThrowAndAssert(
            function () {
                StrategicOutput::at("Valid Name", null);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(StrategicOutput::ERROR_KPA_NULL, $exception->getMessage());
            }
        );
    }

    public function test_strategic_output_kpa_must_be_kpa_instance()
    {
        $this->shouldThrowAndAssert(
            function () {
                StrategicOutput::at("Valid Name", "Not a KPA");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(StrategicOutput::ERROR_KPA_INVALID, $exception->getMessage());
            }
        );
    }

    public function test_strategic_output_name_with_minimum_length_is_valid()
    {
        $strategicOutput = StrategicOutput::at("AI", $this->validKpa);
        
        $this->assertInstanceOf(StrategicOutput::class, $strategicOutput);
        $this->assertEquals("AI", $strategicOutput->getName());
        $this->assertTrue($strategicOutput->validateName());
    }

    public function test_strategic_output_name_with_maximum_length_is_valid()
    {
        $maxName = str_repeat("A", 200); // 200 caracteres exactos
        $strategicOutput = StrategicOutput::at($maxName, $this->validKpa);
        
        $this->assertInstanceOf(StrategicOutput::class, $strategicOutput);
        $this->assertEquals($maxName, $strategicOutput->getName());
        $this->assertTrue($strategicOutput->validateName());
    }

    public function test_strategic_output_name_gets_trimmed()
    {
        $strategicOutput = StrategicOutput::at("  Incrementar productividad  ", $this->validKpa);
        
        $this->assertEquals("Incrementar productividad", $strategicOutput->getName());
    }

    public function test_strategic_output_name_with_unicode_characters_is_valid()
    {
        $strategicOutput = StrategicOutput::at("Mejorar educación técnica", $this->validKpa);
        
        $this->assertInstanceOf(StrategicOutput::class, $strategicOutput);
        $this->assertEquals("Mejorar educación técnica", $strategicOutput->getName());
        $this->assertTrue($strategicOutput->validateName());
    }
}