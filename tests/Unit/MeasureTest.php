<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Kpa;
use App\Models\StrategicOutput;
use App\Models\Measure;
use Exception;
use RuntimeException;

class MeasureTest extends TestCase
{
    private Kpa $validKpa;
    private StrategicOutput $validStrategicOutput;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validKpa = Kpa::at("Desarrollo Rural");
        $this->validStrategicOutput = StrategicOutput::at("Incrementar productividad agrícola", $this->validKpa);
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

    public function test_measure_can_be_created_with_valid_data()
    {
        $measure = Measure::at("Toneladas de cultivo por hectárea", $this->validStrategicOutput);

        $this->assertInstanceOf(Measure::class, $measure);
        $this->assertEquals("Toneladas de cultivo por hectárea", $measure->getName());
        $this->assertEquals($this->validStrategicOutput, $measure->getStrategicOutput());
        $this->assertTrue($measure->validateName());
    }

    public function test_measure_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Measure::at("", $this->validStrategicOutput);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Measure::ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_measure_name_cannot_be_only_spaces()
    {
        $this->shouldThrowAndAssert(
            function () {
                Measure::at("   ", $this->validStrategicOutput);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Measure::ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_measure_name_too_short_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                Measure::at("A", $this->validStrategicOutput);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Measure::ERROR_NAME_MIN_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_measure_name_too_long_throws_runtime_exception()
    {
        $longName = str_repeat("A", 151); // 151 caracteres

        $this->shouldThrowAndAssert(
            function () use ($longName) {
                Measure::at($longName, $this->validStrategicOutput);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Measure::ERROR_NAME_MAX_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_measure_strategic_output_cannot_be_null()
    {
        $this->shouldThrowAndAssert(
            function () {
                Measure::at("Valid Name", null);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Measure::ERROR_STRATEGIC_OUTPUT_NULL, $exception->getMessage());
            }
        );
    }

    public function test_measure_strategic_output_must_be_strategic_output_instance()
    {
        $this->shouldThrowAndAssert(
            function () {
                Measure::at("Valid Name", "Not a StrategicOutput");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Measure::ERROR_STRATEGIC_OUTPUT_INVALID, $exception->getMessage());
            }
        );
    }

    public function test_measure_name_with_minimum_length_is_valid()
    {
        $measure = Measure::at("KG", $this->validStrategicOutput);
        
        $this->assertInstanceOf(Measure::class, $measure);
        $this->assertEquals("KG", $measure->getName());
        $this->assertTrue($measure->validateName());
    }

    public function test_measure_name_with_maximum_length_is_valid()
    {
        $maxName = str_repeat("A", 150); // 150 caracteres exactos
        $measure = Measure::at($maxName, $this->validStrategicOutput);
        
        $this->assertInstanceOf(Measure::class, $measure);
        $this->assertEquals($maxName, $measure->getName());
        $this->assertTrue($measure->validateName());
    }

    public function test_measure_name_gets_trimmed()
    {
        $measure = Measure::at("  Toneladas producidas  ", $this->validStrategicOutput);
        
        $this->assertEquals("Toneladas producidas", $measure->getName());
    }

    public function test_measure_name_with_unicode_characters_is_valid()
    {
        $measure = Measure::at("Porcentaje de niños educados", $this->validStrategicOutput);
        
        $this->assertInstanceOf(Measure::class, $measure);
        $this->assertEquals("Porcentaje de niños educados", $measure->getName());
        $this->assertTrue($measure->validateName());
    }

    public function test_measure_with_different_strategic_outputs()
    {
        $anotherKpa = Kpa::at("Educación");
        $anotherStrategicOutput = StrategicOutput::at("Mejorar alfabetización", $anotherKpa);
        
        $measure = Measure::at("Porcentaje de alfabetización", $anotherStrategicOutput);
        
        $this->assertInstanceOf(Measure::class, $measure);
        $this->assertEquals($anotherStrategicOutput, $measure->getStrategicOutput());
        $this->assertEquals($anotherKpa, $measure->getStrategicOutput()->getKpa());
    }
}