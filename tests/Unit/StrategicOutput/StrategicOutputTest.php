<?php

namespace Tests\Unit\StrategicOutput;

use PHPUnit\Framework\TestCase;
use \App\Modules\StrategicOutput\Domain\StrategicOutput;
use App\Modules\Measure\Domain\Measure;
use Exception;
use RuntimeException;

class StrategicOutputTest extends TestCase
{
    private StrategicOutput $validStrategicOutput;
    private Measure $validMeasure1;
    private Measure $validMeasure2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validStrategicOutput = StrategicOutput::at("Incrementar Productividad");
        $this->validMeasure1 = Measure::at("Toneladas por hectárea",  $this->validStrategicOutput);
        $this->validMeasure2 = Measure::at("Porcentaje de mejora",  $this->validStrategicOutput);
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
        $strategicOutput = StrategicOutput::at("Incrementar productividad agrícola");

        $this->assertInstanceOf(StrategicOutput::class, $strategicOutput);
        $this->assertEquals("Incrementar Productividad Agrícola", $strategicOutput->getName());
    }

    public function test_strategic_output_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                StrategicOutput::at("");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(StrategicOutput::$ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_strategic_output_name_cannot_be_only_spaces()
    {
        $this->shouldThrowAndAssert(
            function () {
                StrategicOutput::at("   ");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(StrategicOutput::$ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_strategic_output_name_too_short_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                StrategicOutput::at("A");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(StrategicOutput::$ERROR_NAME_MIN_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_strategic_output_name_too_long_throws_runtime_exception()
    {
        $longName = str_repeat("A", 301); // 301 caracteres

        $this->shouldThrowAndAssert(
            function () use ($longName) {
                StrategicOutput::at($longName);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(StrategicOutput::$ERROR_NAME_MAX_LENGTH, $exception->getMessage());
            }
        );
    }



    public function test_strategic_output_name_with_minimum_length_is_valid()
    {
        $strategicOutput = StrategicOutput::at("AI");
        
        $this->assertEquals("Ai", $strategicOutput->getName());
    }

    public function test_strategic_output_name_with_maximum_length_is_valid()
    {
        $maxName = str_repeat("A", 200); // 200 caracteres exactos
        $strategicOutput = StrategicOutput::at($maxName);

        $normalizedName = preg_replace('/\s+/', ' ', trim($maxName));
        $capitalName = mb_convert_case($normalizedName, MB_CASE_TITLE, "UTF-8");
        
        $this->assertEquals($capitalName, $strategicOutput->getName());
    }

    public function test_strategic_output_name_gets_trimmed()
    {
        $strategicOutput = StrategicOutput::at("  Incrementar productividad  ");
        
        $this->assertEquals("Incrementar Productividad", $strategicOutput->getName());
    }

    public function test_strategic_output_name_with_unicode_characters_is_valid()
    {
        $strategicOutput = StrategicOutput::at("Mejorar educación técnica");
        
        $this->assertEquals("Mejorar Educación Técnica", $strategicOutput->getName());
    }

    // ===== TESTS CRUD DE MEASURES =====


}