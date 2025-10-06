<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Country;
use App\Models\IndicatorType;
use Exception;
use PHPUnit\Event\Runtime\Runtime;
use RuntimeException;
class IndicatorTypeTest extends TestCase
{
    // cloussure
    public function assertThrows($should, $exception, $assert){
        try{
            $should->__invoke();
            $this->fail();
        }catch (Exception $e){
            $this->assertEquals(get_class($e), $exception);
            $assert->__invoke($e);
        }
    }
    public function test_CanNotCreateIndicatorTypeWithEmptyName(){
        $this->assertThrows(
            function(){ $indicator = IndicatorType::at(""); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), IndicatorType::$NAME_MIN_LENGTH);
            }
        );
    }
    public function test_CanNotCreateIndicatorTypeWithShortName(){
        $this->assertThrows(
            function(){ $indicator = IndicatorType::at("ab"); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), IndicatorType::$NAME_MIN_LENGTH);   
            }
        );
    }
    public function test_canNotCreateIndicatorWithSpacesName(){
        $this->assertThrows(
            function(){ $indicator = IndicatorType::at("   "); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), IndicatorType::$NAME_MIN_LENGTH);
            }
        );
    }
    public function test_CanNotCreateIndicatorNameWithNull(){
        $this->assertThrows(
            function() { $indicator = IndicatorType::at(null); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), IndicatorType::$NAME_MIN_LENGTH);
            }   
        );
    }
    public function test_CanNotCreateIndicatorNameWithNumber(){
        $this->assertThrows(
            function() { $indicator = IndicatorType::at(123);},
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), IndicatorType::$NAME_MUST_BE_STRING);
            }
        );
    }
}