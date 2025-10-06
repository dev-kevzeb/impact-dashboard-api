<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Country;
use Exception;
use RuntimeException;
use App\Models\Indicator;
use App\Models\IndicatorType;
use App\Models\Measure;
use App\Models\StrategicOutput;

class IndicatorTest extends TestCase
{
    private IndicatorType $validIndicatorType;
    private Measure $validMeasure;
    private StrategicOutput $validStrategicOutput;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validIndicatorType = IndicatorType::at("TipoValido");
        $this->validStrategicOutput = StrategicOutput::at("OutputValido", "DescripcionValida");
        $this->validMeasure = new Measure("MedidaValida", $this->validStrategicOutput);
    }
    public function assertThrows($should, $exception, $assert){
        try{
            $should->__invoke();
            $this->fail();
        }catch(Exception $e){
            $this->assertEquals(get_class($e), $exception);
            $assert->__invoke($e);  
        }
    }
    public function test_validate_name_with_only_whitespaces()
    {
        $indicatorType = $this->validIndicatorType;
        $measure = $this->validMeasure;
        $this->assertThrows(
            function() use ($indicatorType, $measure){ 
                Indicator::at("   ", $measure, $indicatorType, 1); 
            },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), Indicator::$NAME_MIN_LENGTH);
            }   
        );
    }

    public function test_validate_name_returns_true_for_valid_name()
    {
        $indicatorType = $this->validIndicatorType;
        $measure = $this->validMeasure;
        $this->assertThrows(
            function() use ($indicatorType, $measure){ Indicator::at("In", $measure, $indicatorType, 1); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), Indicator::$NAME_MIN_LENGTH);
            }   
        );
            
    }   
       public function test_validate_name_not_empty()
    {
        $indicatorType = IndicatorType::at("TipoValido"); 
        $indicatorType = $this->validIndicatorType;
        $measure = $this->validMeasure;
        $this->assertThrows(
            function() use ($indicatorType, $measure){ Indicator::at("", $measure, $indicatorType, 1); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), Indicator::$NAME_MIN_LENGTH);
            }   
        );
            
    }   
    public function test_validate_name_not_null()
    {
        $indicatorType = IndicatorType::at("TipoValido"); 
        $indicatorType = $this->validIndicatorType;
        $measure = $this->validMeasure;
        $this->assertThrows(
            function() use ($indicatorType, $measure){ Indicator::at(null, $measure, $indicatorType, 1) ; },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), Indicator::$NAME_MIN_LENGTH);
            }
        );
    }
    public function test_validate_measure_not_empty()
    {
        $indicatorType = IndicatorType::at("TipoValido"); 
        $indicatorType = $this->validIndicatorType; 
        $measure = $this->validMeasure;
        $this->assertThrows(
            function() use ($indicatorType, $measure){ Indicator::at("IndicadorValido", " ", $indicatorType, 1); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), Indicator::$INSTANCE_OF_MEASURE);
            }   
        );
            
    }
    public function test_validate_measure_not_null()
    {
        $indicatorType = IndicatorType::at("TipoValido"); 
        $indicatorType = $this->validIndicatorType;
        // $measure = $this->validMeasure;
        $this->assertThrows(
            function() use ($indicatorType){ Indicator::at("IndicadorValido", null, $indicatorType, 1); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), Indicator::$INSTANCE_OF_MEASURE);
            }   
        );
            
    }
    public function test_validate_indicator_type_not_null()
    {   
        // $indicatorType = $this->validIndicatorType;
        $measure = $this->validMeasure;
        $this->assertThrows(
            function() use($measure){ Indicator::at("IndicadorValido", $measure, null, 1); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), Indicator::$INSTANCE_OF_INDICATORTYPE);
            }   
        );
            
    }
    public function test_validate_indicator_type_are_instance_of_indicator_type()
    {
        // $indicatorType = $this->validIndicatorType;
        $measure = $this->validMeasure;
        $this->assertThrows(
            function() use($measure){ Indicator::at("IndicadorValido", $measure, "NotAnIndicatorType", 1); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), Indicator::$INSTANCE_OF_INDICATORTYPE);
            }   
        );
    }
    public function test_validate_numeric_parameter_is_positive()
    {
        $indicatorType = $this->validIndicatorType;
        $measure = $this->validMeasure;
        $this->assertThrows(
            function() use ($indicatorType, $measure){ 
                Indicator::at("IndicadorValido", $measure, $indicatorType, -1); 
            },
            RuntimeException::class,
            function($e){
                $this->assertStringContainsString("positive", strtolower($e->getMessage()));
            }   
        );
    }
    public function test_validate_measure_is_instance_of_measure()
    {
        $indicatorType = $this->validIndicatorType;
        $this->assertThrows(
            function() use ($indicatorType){ 
                Indicator::at("IndicadorValido", new \stdClass(), $indicatorType, 1); 
            },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), Indicator::$INSTANCE_OF_MEASURE);
            }   
        );
    }
    public function test_validate_numeric_parameter_is_numeric()
    {
        $indicatorType = $this->validIndicatorType;
        $measure = $this->validMeasure;
        $this->assertThrows(
            function() use ($indicatorType, $measure){ 
                Indicator::at("IndicadorValido", $measure, $indicatorType, "not a number"); 
            },
            RuntimeException::class,
            function($e){
                $this->assertStringContainsString("numeric", strtolower($e->getMessage()));
            }   
        );
    }
}