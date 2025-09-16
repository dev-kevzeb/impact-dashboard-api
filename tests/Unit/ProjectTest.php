<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Country;
use Exception;
use RuntimeException;

class ProjectTest extends TestCase
{
    // bloque de codigo, el manejo de errores, forma en que manejamos el error
    public function shouldThrowAndAssert($should,$exceptionType,$assertions){
        try {
            $should->__invoke();
            $this->fail();
        } catch (Exception $exception) {
            $this->assertEquals($exceptionType,  get_class($exception));
            $assertions->__invoke($exception);
        }
    }

   
    public function test_validate_name_returns_true_for_valid_name()
    {
        $country = Country::at("Argentina");
        $this->assertTrue($country->validateName());
    }

    public function test_validate_name_returns_false_for_short_name()
    {
        $country = Country::at("");
        $this->assertFalse($country->validateName());
    }
    public function test_validate_name_returns_false_for_short_name2()
    {
        $this->shouldThrowAndAssert(
            function () { $country = Country::at(NULL); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'el nombre del pais no debe ser null');
            });

    }

 
}
