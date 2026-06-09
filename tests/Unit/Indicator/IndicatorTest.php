<?php

namespace Tests\Unit\Indicator;

use App\Modules\Indicator\Domain\Indicator;
use App\Modules\IndicatorType\Domain\IndicatorType;
use App\Modules\Measure\Domain\Measure;
use App\Modules\StrategicOutput\Domain\StrategicOutput;
use PHPUnit\Framework\TestCase;
use Exception;
use RuntimeException;

class IndicatorTest extends TestCase
{
    private IndicatorType $validIndicatorType;
    private Measure $validMeasure;
    private StrategicOutput $validStrategicOutput;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validIndicatorType = IndicatorType::at("TipoValido");
        $this->validStrategicOutput = StrategicOutput::at("Estrategia valida");
        $this->validMeasure = Measure::at($this->validIndicatorType, $this->validStrategicOutput);

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
    public function test_indicator_name_cannot_be_empty()
    {
        $indicatorType = $this->validIndicatorType;
        $this->shouldThrowAndAssert(
            function() use ($indicatorType){ 
                Indicator::at("", $indicatorType, 1, $this->validMeasure); 
            },
            RuntimeException::class,
            function($exception){
                $this->assertEquals(Indicator::$ERROR_NAME_EMPTY, $exception->getMessage());
            }   
        );
    }

    public function test_indicator_name_cannot_be_only_spaces()
    {
        $indicatorType = $this->validIndicatorType;
        $this->shouldThrowAndAssert(
            function() use ($indicatorType){ 
                Indicator::at("   ", $indicatorType, 1, $this->validMeasure); 
            },
            RuntimeException::class,
            function($exception){
                $this->assertEquals(Indicator::$ERROR_NAME_EMPTY, $exception->getMessage());
            }   
        );
    }

    public function test_indicator_name_too_short_throws_runtime_exception()
    {
        $indicatorType = $this->validIndicatorType;
        $this->shouldThrowAndAssert(
            function() use ($indicatorType){ Indicator::at("I", $indicatorType, 1, $this->validMeasure); },
            RuntimeException::class,
            function($exception){
                $this->assertEquals(Indicator::$ERROR_NAME_MIN_LENGTH, $exception->getMessage());
            }   
        );
    }   

    public function test_indicator_type_cannot_be_null()
    {   
        $this->shouldThrowAndAssert(
            function(){ Indicator::at("IndicadorValido", null, 1, $this->validMeasure); },
            RuntimeException::class,
            function($exception){
                $this->assertEquals(Indicator::$ERROR_TYPE_REQUIRED, $exception->getMessage());
            }   
        );
    }

    public function test_measure_cannot_be_null()
    {   
        $this->shouldThrowAndAssert(
            function(){ Indicator::at("IndicadorValido", $this->validIndicatorType, 1, null); },
            RuntimeException::class,
            function($exception){
                $this->assertEquals(Indicator::$ERROR_MEASURE_REQUIRED, $exception->getMessage());
            }   
        );
    }

    public function test_indicator_type_must_be_instance_of_indicator_type()
    {
        $this->shouldThrowAndAssert(
            function(){ Indicator::at("IndicadorValido", "NotAnIndicatorType", 1, $this->validMeasure); },
            RuntimeException::class,
            function($exception){
                $this->assertEquals(Indicator::$ERROR_TYPE_REQUIRED, $exception->getMessage());
            }   
        );
    }

    public function test_indicator_target_must_be_positive()
    {
        $indicatorType = $this->validIndicatorType;
        $this->shouldThrowAndAssert(
            function() use ($indicatorType){ 
                Indicator::at("IndicadorValido", $indicatorType, -1, $this->validMeasure); 
            },
            RuntimeException::class,
            function($exception){
                $this->assertEquals(Indicator::$ERROR_TARGET_INVALID, $exception->getMessage());
            }   
        );
    }

    public function test_indicator_target_must_be_numeric()
    {
        $indicatorType = $this->validIndicatorType;
        $this->shouldThrowAndAssert(
            function() use ($indicatorType){ 
                Indicator::at("IndicadorValido", $indicatorType, "not a number", $this->validMeasure); 
            },
            RuntimeException::class,
            function($exception){
                $this->assertEquals(Indicator::$ERROR_TARGET_INVALID, $exception->getMessage());
            }   
        );
    }

    public function test_indicator_can_be_created_with_valid_data()
    {
        $indicatorType = $this->validIndicatorType;
        $measure = $this->validMeasure;
        
        $indicator = Indicator::at("Indicador Valido", $indicatorType, 100, $measure);
        
        $this->assertInstanceOf(Indicator::class, $indicator);
        
        //Usan conexion SQL ya no son utiles estos datos
        //$this->assertEquals("Indicador Valido", $indicator->getName());
        //$this->assertEquals($indicatorType, $indicator->getType());
        //$this->assertEquals($measure, $indicator->getMeasure());
    }

    public function test_indicator_name_too_long_throws_runtime_exception()
    {
        $indicatorType = $this->validIndicatorType;
        $longName = str_repeat("A", 301); // 301 caracteres
        
        $this->shouldThrowAndAssert(
            function() use ($indicatorType, $longName){ 
                Indicator::at($longName, $indicatorType, 1, $this->validMeasure); 
            },
            RuntimeException::class,
            function($exception){
                $this->assertEquals(Indicator::$ERROR_NAME_MAX_LENGTH, $exception->getMessage());
            }   
        );
    }

    public function test_indicator_name_with_minimum_length_is_valid()
    {
        $indicatorType = $this->validIndicatorType;
        
        $indicator = Indicator::at("AB", $indicatorType, 1, $this->validMeasure);
        
        $this->assertEquals("AB", $indicator->getName());
    }

    public function test_indicator_name_with_maximum_length_is_valid()
    {
        $indicatorType = $this->validIndicatorType;
        $maxName = str_repeat("A", 200); // 200 caracteres exactos
        
        $indicator = Indicator::at($maxName, $indicatorType, 50, $this->validMeasure);
        
        $this->assertEquals($maxName, $indicator->getName());
    }

    public function test_indicator_trims_name_when_created()
    {
        $indicatorType = $this->validIndicatorType;
        
        $indicator = Indicator::at("  Indicador Con Espacios  ", $indicatorType, 50, $this->validMeasure);
        
        $this->assertEquals("Indicador Con Espacios", $indicator->getName());
    }

    public function test_indicator_name_with_unicode_characters_is_valid()
    {
        $indicatorType = $this->validIndicatorType;
        
        $indicator = Indicator::at("Indicador con niños educados", $indicatorType, 75, $this->validMeasure);
        
        $this->assertEquals("Indicador con niños educados", $indicator->getName());
    }
}