<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Country;
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
            function(){ $country = Country::at(""); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), 'el nombre del tipo de indicador no debe ser null o menor a 3 caracteres');
            }
        );
    }
    public function test_CanNotCreateIndicatorTypeWithShortName(){
        $this->assertThrows(
            function(){ $country = Country::at("ab"); },
            RuntimeException::class,
            function($e){
                $this->assertEquals($e->getMessage(), 'el nombre del tipo de indicador no debe ser null o menor a 3 caracteres');   
            }
        );
    }
}