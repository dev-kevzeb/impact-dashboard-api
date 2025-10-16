<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Kpa;
use App\Models\StrategicOutput;
use App\Models\Measure;
use Exception;
use RuntimeException;

class StrategicOutputTest extends TestCase
{
    private Kpa $validKpa;
    private StrategicOutput $validStrategicOutput;
    private Measure $validMeasure1;
    private Measure $validMeasure2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validKpa = Kpa::at("Desarrollo Rural", 75.0);
        $this->validStrategicOutput = StrategicOutput::at("Incrementar Productividad", $this->validKpa);
        $this->validMeasure1 = Measure::at("Toneladas por hectárea", $this->validStrategicOutput);
        $this->validMeasure2 = Measure::at("Porcentaje de mejora", $this->validStrategicOutput);
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
    }

    public function test_strategic_output_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                StrategicOutput::at("", $this->validKpa);
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
                StrategicOutput::at("   ", $this->validKpa);
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
                StrategicOutput::at("A", $this->validKpa);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(StrategicOutput::$ERROR_NAME_MIN_LENGTH, $exception->getMessage());
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
                $this->assertEquals(StrategicOutput::$ERROR_NAME_MAX_LENGTH, $exception->getMessage());
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
                $this->assertEquals(StrategicOutput::$ERROR_KPA_INVALID, $exception->getMessage());
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
                $this->assertEquals(StrategicOutput::$ERROR_KPA_INVALID, $exception->getMessage());
            }
        );
    }

    public function test_strategic_output_name_with_minimum_length_is_valid()
    {
        $strategicOutput = StrategicOutput::at("AI", $this->validKpa);
        
        $this->assertEquals("AI", $strategicOutput->getName());
    }

    public function test_strategic_output_name_with_maximum_length_is_valid()
    {
        $maxName = str_repeat("A", 200); // 200 caracteres exactos
        $strategicOutput = StrategicOutput::at($maxName, $this->validKpa);
        
        $this->assertEquals($maxName, $strategicOutput->getName());
    }

    public function test_strategic_output_name_gets_trimmed()
    {
        $strategicOutput = StrategicOutput::at("  Incrementar productividad  ", $this->validKpa);
        
        $this->assertEquals("Incrementar productividad", $strategicOutput->getName());
    }

    public function test_strategic_output_name_with_unicode_characters_is_valid()
    {
        $strategicOutput = StrategicOutput::at("Mejorar educación técnica", $this->validKpa);
        
        $this->assertEquals("Mejorar educación técnica", $strategicOutput->getName());
    }

    // ===== TESTS CRUD DE MEASURES =====

    public function test_strategic_output_can_be_created_without_measures()
    {
        $this->assertEquals([], $this->validStrategicOutput->getMeasures());
    }

    public function test_strategic_output_can_add_measure_with_valid_instance()
    {
        $this->validStrategicOutput->addMeasure($this->validMeasure1);
        
        $this->assertEquals([$this->validMeasure1], $this->validStrategicOutput->getMeasures());
    }

    public function test_strategic_output_add_measure_with_invalid_instance_throws_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->validStrategicOutput->addMeasure("not a measure");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(StrategicOutput::$ERROR_MEASURE_INVALID_INSTANCE, $exception->getMessage());
            }
        );
    }

    public function test_strategic_output_add_measure_with_duplicate_name_throws_exception()
    {
        $duplicateMeasure = Measure::at("Toneladas por hectárea", $this->validStrategicOutput);
        
        $this->validStrategicOutput->addMeasure($this->validMeasure1);
        
        $this->shouldThrowAndAssert(
            function () use ($duplicateMeasure) {
                $this->validStrategicOutput->addMeasure($duplicateMeasure);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(StrategicOutput::$ERROR_MEASURES_DUPLICATED, $exception->getMessage());
            }
        );
    }

    public function test_strategic_output_can_add_multiple_measures()
    {
        $measure3 = Measure::at("Ingresos por familia", $this->validStrategicOutput);
        
        $this->validStrategicOutput->addMeasure($this->validMeasure1);
        $this->validStrategicOutput->addMeasure($this->validMeasure2);
        $this->validStrategicOutput->addMeasure($measure3);
        
        $this->assertEquals([$this->validMeasure1, $this->validMeasure2, $measure3], $this->validStrategicOutput->getMeasures());
    }

    public function test_strategic_output_find_measure_by_name_found()
    {
        $this->validStrategicOutput->addMeasure($this->validMeasure1);
        
        $found = $this->validStrategicOutput->findMeasureByName("Toneladas por hectárea");
        
        $this->assertInstanceOf(Measure::class, $found);
        $this->assertEquals($this->validMeasure1, $found);
        $this->assertEquals("Toneladas por hectárea", $found->getName());
    }

    public function test_strategic_output_find_measure_by_name_not_found()
    {
        $this->validStrategicOutput->addMeasure($this->validMeasure1);
        
        $found = $this->validStrategicOutput->findMeasureByName("No Existe");
        
        $this->assertNull($found);
    }

    public function test_strategic_output_remove_measure_existing_returns_true()
    {
        $this->validStrategicOutput->addMeasure($this->validMeasure1);
        $this->validStrategicOutput->addMeasure($this->validMeasure2);
        
        $result = $this->validStrategicOutput->removeMeasure("Toneladas por hectárea");
        
        $this->assertTrue($result);
        $this->assertEquals([$this->validMeasure2], $this->validStrategicOutput->getMeasures());
    }

    public function test_strategic_output_remove_measure_non_existing_returns_false()
    {
        $this->validStrategicOutput->addMeasure($this->validMeasure1);
        
        $result = $this->validStrategicOutput->removeMeasure("No Existe");
        
        $this->assertFalse($result);
        $this->assertEquals([$this->validMeasure1], $this->validStrategicOutput->getMeasures());
    }

    public function test_strategic_output_clear_measures_with_elements()
    {
        $this->validStrategicOutput->addMeasure($this->validMeasure1);
        $this->validStrategicOutput->addMeasure($this->validMeasure2);
        
        $this->validStrategicOutput->clearMeasures();
        
        $this->assertEquals([], $this->validStrategicOutput->getMeasures());
    }

    public function test_strategic_output_clear_measures_with_empty_array()
    {
        $this->validStrategicOutput->clearMeasures(); 
        
        $this->assertEquals([], $this->validStrategicOutput->getMeasures());
    }
}