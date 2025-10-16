<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Kpa;
use App\Models\StrategicOutput;
use App\Models\Measure;
use App\Models\Indicator;
use App\Models\IndicatorType;
use Exception;
use RuntimeException;

class MeasureTest extends TestCase
{
    private Kpa $validKpa;
    private StrategicOutput $validStrategicOutput;
    private Measure $validMeasure;
    private IndicatorType $validIndicatorType;
    private Indicator $validIndicator1;
    private Indicator $validIndicator2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validKpa = Kpa::at("Desarrollo Rural", 80.0);
        $this->validStrategicOutput = StrategicOutput::at("Incrementar productividad agrícola", $this->validKpa);
        $this->validMeasure = Measure::at("Toneladas por hectárea", $this->validStrategicOutput);
        $this->validIndicatorType = IndicatorType::at("Porcentual");
        $this->validIndicator1 = Indicator::at("Aumento productividad", $this->validMeasure, $this->validIndicatorType, 25);
        $this->validIndicator2 = Indicator::at("Reducción costos", $this->validMeasure, $this->validIndicatorType, 15);
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
        $longName = str_repeat("A", 151); // 151 caracteres

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

    public function test_measure_strategic_output_cannot_be_null()
    {
        $this->shouldThrowAndAssert(
            function () {
                Measure::at("Valid Name", null);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Measure::$ERROR_STRATEGIC_OUTPUT_INVALID, $exception->getMessage());
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
                $this->assertEquals(Measure::$ERROR_STRATEGIC_OUTPUT_INVALID, $exception->getMessage());
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

    public function test_measure_with_different_strategic_outputs()
    {
        $anotherKpa = Kpa::at("Educación", 90.0);
        $anotherStrategicOutput = StrategicOutput::at("Mejorar alfabetización", $anotherKpa);
        
        $measure = Measure::at("Porcentaje de alfabetización", $anotherStrategicOutput);
        
        $this->assertEquals($anotherStrategicOutput, $measure->getStrategicOutput());
        $this->assertEquals($anotherKpa, $measure->getStrategicOutput()->getKpa());
    }

    public function test_measure_can_be_created_without_indicators()
    {
        $this->assertEquals([], $this->validMeasure->getIndicators());
    }

    public function test_measure_can_add_indicator_with_valid_instance()
    {
        $this->validMeasure->addIndicator($this->validIndicator1);
        
        $this->assertEquals([$this->validIndicator1], $this->validMeasure->getIndicators());
    }

    public function test_measure_add_indicator_with_invalid_instance_throws_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->validMeasure->addIndicator("not an indicator");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Measure::$ERROR_INDICATOR_INVALID_INSTANCE, $exception->getMessage());
            }
        );
    }

    public function test_measure_add_indicator_with_duplicate_name_throws_exception()
    {
        $duplicateIndicator = Indicator::at("Aumento productividad", $this->validMeasure, $this->validIndicatorType, 30);
        
        $this->validMeasure->addIndicator($this->validIndicator1);
        
        $this->shouldThrowAndAssert(
            function () use ($duplicateIndicator) {
                $this->validMeasure->addIndicator($duplicateIndicator);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Measure::$ERROR_INDICATORS_DUPLICATED, $exception->getMessage());
            }
        );
    }

    public function test_measure_can_add_multiple_indicators()
    {
        $indicator3 = Indicator::at("Mejora calidad", $this->validMeasure, $this->validIndicatorType, 20);
        
        $this->validMeasure->addIndicator($this->validIndicator1);
        $this->validMeasure->addIndicator($this->validIndicator2);
        $this->validMeasure->addIndicator($indicator3);
        
        $this->assertEquals([$this->validIndicator1, $this->validIndicator2, $indicator3], $this->validMeasure->getIndicators());
    }

    public function test_measure_find_indicator_by_name_found()
    {
        $this->validMeasure->addIndicator($this->validIndicator1);
        
        $found = $this->validMeasure->findIndicatorByName("Aumento productividad");
        
        $this->assertInstanceOf(Indicator::class, $found);
        $this->assertEquals($this->validIndicator1, $found);
        $this->assertEquals("Aumento productividad", $found->getName());
    }

    public function test_measure_find_indicator_by_name_not_found()
    {
        $this->validMeasure->addIndicator($this->validIndicator1);
        
        $found = $this->validMeasure->findIndicatorByName("No Existe");
        
        $this->assertNull($found);
    }

    public function test_measure_remove_indicator_existing_returns_true()
    {
        $this->validMeasure->addIndicator($this->validIndicator1);
        $this->validMeasure->addIndicator($this->validIndicator2);
        
        $result = $this->validMeasure->removeIndicator("Aumento productividad");
        
        $this->assertTrue($result);
        $this->assertEquals([$this->validIndicator2], $this->validMeasure->getIndicators());
    }

    public function test_measure_remove_indicator_non_existing_returns_false()
    {
        $this->validMeasure->addIndicator($this->validIndicator1);
        
        $result = $this->validMeasure->removeIndicator("No Existe");
        
        $this->assertFalse($result);
        $this->assertEquals([$this->validIndicator1], $this->validMeasure->getIndicators());
    }

    public function test_measure_clear_indicators_with_elements()
    {
        $this->validMeasure->addIndicator($this->validIndicator1);
        $this->validMeasure->addIndicator($this->validIndicator2);
        
        $this->validMeasure->clearIndicators();
        
        $this->assertEquals([], $this->validMeasure->getIndicators());
    }

    public function test_measure_clear_indicators_with_empty_array()
    {
        $this->validMeasure->clearIndicators(); 
        
        $this->assertEquals([], $this->validMeasure->getIndicators());
    }
}