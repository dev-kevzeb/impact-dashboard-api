<?php

namespace Tests\Unit\Measure;

use App\Modules\Indicator\Domain\Indicator;
use App\Modules\IndicatorType\Domain\IndicatorType;
use App\Modules\Measure\Domain\Measure;
use App\Modules\StrategicOutput\Domain\StrategicOutput;
use PHPUnit\Framework\TestCase;
use Exception;
use RuntimeException;

class MeasureTest extends TestCase
{
    private Measure $validMeasure;
    private IndicatorType $validIndicatorType;
    private Indicator $validIndicator1;
    private Indicator $validIndicator2;
    private StrategicOutput $validStrategicOutput;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validStrategicOutput = StrategicOutput::at("Estrategia valida");
        $this->validMeasure = Measure::at("Toneladas por hectárea", $this->validStrategicOutput);
        $this->validIndicatorType = IndicatorType::at("Porcentual");
        $this->validIndicator1 = Indicator::at("Aumento productividad", $this->validIndicatorType, 25, $this->validMeasure);
        $this->validIndicator2 = Indicator::at("Reducción costos", $this->validIndicatorType, 15, $this->validMeasure);
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
        $strategicOutput = $this->validStrategicOutput;
        $measure = Measure::at("Toneladas de cultivo por hectárea", $strategicOutput);

        $this->assertInstanceOf(Measure::class, $measure);
        $this->assertEquals("Toneladas de cultivo por hectárea", $measure->getName());
    }

    public function test_measure_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Measure::at("", $this->validStrategicOutput);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Measure::$ERROR_NAME_EMPTY, $exception->getMessage());
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
                $this->assertEquals(Measure::$ERROR_NAME_EMPTY, $exception->getMessage());
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
                $this->assertEquals(Measure::$ERROR_NAME_MIN_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_measure_name_too_long_throws_runtime_exception()
    {
        $longName = str_repeat("A", 301); // 301 caracteres

        $this->shouldThrowAndAssert(
            function () use ($longName) {
                Measure::at($longName, $this->validStrategicOutput);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Measure::$ERROR_NAME_MAX_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_measure_name_with_minimum_length_is_valid()
    {
        $measure = Measure::at("KG", $this->validStrategicOutput);
        
        $this->assertEquals("KG", $measure->getName());
    }

    public function test_measure_name_with_maximum_length_is_valid()
    {
        $maxName = str_repeat("A", 150); // 150 caracteres exactos
        $measure = Measure::at($maxName, $this->validStrategicOutput);
        
        $this->assertEquals($maxName, $measure->getName());
    }

    public function test_measure_name_gets_trimmed()
    {
        $measure = Measure::at("  Toneladas producidas  ", $this->validStrategicOutput);
        
        $this->assertEquals("Toneladas producidas", $measure->getName());
    }

    public function test_measure_name_with_unicode_characters_is_valid()
    {
        $measure = Measure::at("Porcentaje de niños educados", $this->validStrategicOutput);
        
        $this->assertEquals("Porcentaje de niños educados", $measure->getName());
    }  
}