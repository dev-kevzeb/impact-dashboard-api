<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Kpa;
use App\Models\StrategicOutput;
use Exception;
use RuntimeException;

class KpaTest extends TestCase
{   
    private Kpa $validKpa;
    private StrategicOutput $validStrategicOutput1;
    private StrategicOutput $validStrategicOutput2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validKpa = Kpa::at("Desarrollo Rural", 75.0);
        $this->validStrategicOutput1 = StrategicOutput::at("Incrementar Productividad", $this->validKpa);
        $this->validStrategicOutput2 = StrategicOutput::at("Mejorar Seguridad Alimentaria", $this->validKpa);
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

    public function test_kpa_can_be_created_with_valid_name()
    {
        $kpa = Kpa::at("Desarrollo Rural", 50);

        $this->assertInstanceOf(Kpa::class, $kpa);
        $this->assertEquals("Desarrollo Rural", $kpa->getName());
    }

    public function test_kpa_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Kpa::at("", 50);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Kpa::$ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_kpa_name_cannot_be_only_spaces()
    {
        $this->shouldThrowAndAssert(
            function () {
                Kpa::at("   ", 50);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Kpa::$ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_kpa_name_too_short_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                Kpa::at("A", 50);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Kpa::$ERROR_NAME_MIN_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_kpa_name_too_long_throws_runtime_exception()
    {
        $longName = str_repeat("A", 101); // 101 caracteres

        $this->shouldThrowAndAssert(
            function () use ($longName) {
                Kpa::at($longName, 50);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Kpa::$ERROR_NAME_MAX_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_kpa_name_with_minimum_length_is_valid()
    {
        $kpa = Kpa::at("AI", 50);
        
        $this->assertInstanceOf(Kpa::class, $kpa);
        $this->assertEquals("AI", $kpa->getName());
    }

    public function test_kpa_name_with_maximum_length_is_valid()
    {
        $maxName = str_repeat("A", 100); // 100 caracteres exactos
        $kpa = Kpa::at($maxName, 50);
        
        $this->assertInstanceOf(Kpa::class, $kpa);
        $this->assertEquals($maxName, $kpa->getName());
    }

    public function test_kpa_name_with_special_characters_is_valid()
    {
        $kpa = Kpa::at("Desarrollo Rural & Sostenible" , 50);
        
        $this->assertInstanceOf(Kpa::class, $kpa);
        $this->assertEquals("Desarrollo Rural & Sostenible", $kpa->getName());
    }

    public function test_kpa_name_with_unicode_characters_is_valid()
    {
        $kpa = Kpa::at("Educación y Nutrición", 50);
        
        $this->assertInstanceOf(Kpa::class, $kpa);
        $this->assertEquals("Educación y Nutrición", $kpa->getName());
    }

    public function test_kpa_name_gets_trimmed()
    {
        $kpa = Kpa::at("  Desarrollo Rural  ", 50);
        
        $this->assertEquals("Desarrollo Rural", $kpa->getName());
    }
    public function test_kpa_implementation_cannot_be_negative(){
        $this->shouldThrowAndAssert(
            function () { Kpa::at("Desarrollo Rural", -10); },
            RuntimeException::class,
            function ($exception) { $this->assertEquals(Kpa::$ERROR_IMPLEMENTATION_OUT_OF_RANGE, $exception->getMessage()); }
        );
    }
    public function test_kpa_implementation_cannot_exceed_100(){
        $this->shouldThrowAndAssert(
            function () { Kpa::at("Desarrollo Rural", 150); },
            RuntimeException::class,
            function ($exception) { $this->assertEquals(Kpa::$ERROR_IMPLEMENTATION_OUT_OF_RANGE, $exception->getMessage()); }
        );
    }
    public function test_kpa_implementation_must_be_numeric(){
        $this->shouldThrowAndAssert(
            function () { Kpa::at("Desarrollo Rural", "Hola"); },
            RuntimeException::class,
            function ($exception) { $this->assertEquals(Kpa::$ERROR_IMPLEMENTATION_NOT_NUMERIC, $exception->getMessage()); }
        );  
    }
    
    public function test_kpa_can_be_created_without_strategic_outputs(){
        $kpa = Kpa::at("Desarrollo Rural", 50);
        $this->assertInstanceOf(Kpa::class, $kpa);
        $this->assertEquals("Desarrollo Rural", $kpa->getName());
        $this->assertEquals(50, $kpa->getImplementation());
        $this->assertEquals([], $kpa->getStrategicOutputs());
    }

    public function test_kpa_can_add_strategic_output_with_valid_instance()
    {
        $this->validKpa->addStrategicOutput($this->validStrategicOutput1);
        
        $this->assertEquals([$this->validStrategicOutput1], $this->validKpa->getStrategicOutputs());
    }

    public function test_kpa_add_strategic_output_with_invalid_instance_throws_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->validKpa->addStrategicOutput("not a strategic output");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Kpa::$ERROR_STRATEGIC_OUTPUT_INVALID_INSTANCE, $exception->getMessage());
            }
        );
    }

    public function test_kpa_add_strategic_output_with_duplicate_name_throws_exception()
    {
        $duplicateOutput = StrategicOutput::at("Incrementar Productividad", $this->validKpa);
        
        $this->validKpa->addStrategicOutput($this->validStrategicOutput1);
        
        $this->shouldThrowAndAssert(
            function () use ($duplicateOutput) {
                $this->validKpa->addStrategicOutput($duplicateOutput);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Kpa::$ERROR_STRATEGIC_OUTPUTS_DUPLICATED, $exception->getMessage());
            }
        );
    }

    public function test_kpa_can_add_multiple_strategic_outputs()
    {
        $output3 = StrategicOutput::at("Fortalecer Capacidades", $this->validKpa);
        
        $this->validKpa->addStrategicOutput($this->validStrategicOutput1);
        $this->validKpa->addStrategicOutput($this->validStrategicOutput2);  
        $this->validKpa->addStrategicOutput($output3);
        
        $this->assertEquals([$this->validStrategicOutput1, $this->validStrategicOutput2, $output3], $this->validKpa->getStrategicOutputs());
    }

    public function test_kpa_find_strategic_output_by_name_found()
    {
        $this->validKpa->addStrategicOutput($this->validStrategicOutput1);
        
        $found = $this->validKpa->findStrategicOutputByName("Incrementar Productividad");
        
        $this->assertInstanceOf(StrategicOutput::class, $found);
        $this->assertEquals($this->validStrategicOutput1, $found);
        $this->assertEquals("Incrementar Productividad", $found->getName());
    }

    public function test_kpa_find_strategic_output_by_name_not_found()
    {
        $this->validKpa->addStrategicOutput($this->validStrategicOutput1);
        
        $found = $this->validKpa->findStrategicOutputByName("No Existe");
        
        $this->assertNull($found);
    }

    public function test_kpa_remove_strategic_output_existing_returns_true()
    {
        $this->validKpa->addStrategicOutput($this->validStrategicOutput1);
        $this->validKpa->addStrategicOutput($this->validStrategicOutput2);
        
        $result = $this->validKpa->removeStrategicOutput("Incrementar Productividad");
        
        $this->assertTrue($result);
        $this->assertEquals([$this->validStrategicOutput2], $this->validKpa->getStrategicOutputs());
    }

    public function test_kpa_remove_strategic_output_non_existing_returns_false()
    {
        $this->validKpa->addStrategicOutput($this->validStrategicOutput1);
        
        $result = $this->validKpa->removeStrategicOutput("No Existe");
        
        $this->assertFalse($result);
        $this->assertEquals([$this->validStrategicOutput1], $this->validKpa->getStrategicOutputs());
    }

    public function test_kpa_clear_strategic_outputs_with_elements()
    {
        $this->validKpa->addStrategicOutput($this->validStrategicOutput1);
        $this->validKpa->addStrategicOutput($this->validStrategicOutput2);
        
        $this->validKpa->clearStrategicOutputs();
        
        $this->assertEquals([], $this->validKpa->getStrategicOutputs());
    }

    public function test_kpa_clear_strategic_outputs_with_empty_array()
    {
        $this->validKpa->clearStrategicOutputs(); 
        
        $this->assertEquals([], $this->validKpa->getStrategicOutputs());
    }

}