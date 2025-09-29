<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Country;
use Exception;
use RuntimeException;
use App\Models\Indicator;
use App\Models\IndicatorType;

class IndicatorTest extends TestCase
{
    public function assertThrows($should, $exception, $assert){
        try{
            $should->__invoke();
            $this->fail();
        }catch(Exception $e){
            $this->assertEquals(get_class($e), $exception);
            $assert->__invoke($e);  
        }
    }

    public function test_validate_name_returns_true_for_valid_name()
    {
        $indicatorType = IndicatorType::at("TipoValido"); 
        $indicator = Indicator::at("IndicadorValido", "MedidaValida", $indicatorType);

        $this->assertThrows(
            function() use ($indicatorType){ Indicator::at("In", "MedidaValida", $indicatorType); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), 'el nombre del indicador no debe ser null o menor a 3 caracteres');
            }   
        );
            
    }   
       public function test_validate_name_not_empty()
    {
        $indicatorType = IndicatorType::at("TipoValido"); 

        $this->assertThrows(
            function() use ($indicatorType){ Indicator::at("", "MedidaValida", $indicatorType); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), 'el nombre del indicador no debe ser null o menor a 3 caracteres');
            }   
        );
            
    }   
    public function test_validate_name_not_null()
    {
        $indicatorType = IndicatorType::at("TipoValido"); 
        $this->assertThrows(
            function() use ($indicatorType){ Indicator::at(null, "MedidaValida", $indicatorType); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), 'el nombre del indicador no debe ser null o menor a 3 caracteres');
            }
        );
    }
    public function test_validate_measure_not_empty()
    {
        $indicatorType = IndicatorType::at("TipoValido"); 

        $this->assertThrows(
            function() use ($indicatorType){ Indicator::at("IndicadorValido", "", $indicatorType); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), 'la medida del indicador no debe ser null o menor a 3 caracteres');
            }   
        );
            
    }
    public function test_validate_measure_not_null()
    {
        $indicatorType = IndicatorType::at("TipoValido"); 

        $this->assertThrows(
            function() use ($indicatorType){ Indicator::at("IndicadorValido", null, $indicatorType); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), 'la medida del indicador no debe ser null o menor a 3 caracteres');
            }   
        );
            
    }
    public function test_validate_indicator_type_not_null()
    {
        $this->assertThrows(
            function(){ Indicator::at("IndicadorValido", "MedidaValida", null); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), 'el tipo de indicador no debe ser null');
            }   
        );
            
    }
    public function test_validate_indicator_type_are_instance_of_indicator_type()
    {
        $this->assertThrows(
            function(){ Indicator::at("IndicadorValido", "MedidaValida", "NotAnIndicatorType"); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), 'el tipo de indicador debe ser una instancia de IndicatorType');
            }   
        );
    }
}