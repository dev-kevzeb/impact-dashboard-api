<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\IndicatorType;
use Exception;
use RuntimeException;

class IndicatorTypeTest extends TestCase
{
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

    public function test_indicator_type_can_be_created_with_valid_data()
    {
        $indicatorType = IndicatorType::at("Tipo Valido");
        
        $this->assertInstanceOf(IndicatorType::class, $indicatorType);
        $this->assertEquals("Tipo Valido", $indicatorType->getName());
    }

    public function test_indicator_type_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function() { 
                IndicatorType::at(""); 
            },
            RuntimeException::class,
            function($exception) {
                $this->assertEquals(IndicatorType::$ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_indicator_type_name_cannot_be_only_spaces()
    {
        $this->shouldThrowAndAssert(
            function() { 
                IndicatorType::at("   "); 
            },
            RuntimeException::class,
            function($exception) {
                $this->assertEquals(IndicatorType::$ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_indicator_type_name_too_short_throws_exception()
    {
        $this->shouldThrowAndAssert(
            function() { 
                IndicatorType::at("A"); 
            },
            RuntimeException::class,
            function($exception) {
                $this->assertEquals(IndicatorType::$ERROR_NAME_MIN_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_indicator_type_name_too_long_throws_exception()
    {
        $longName = str_repeat("A", 101); // 101 caracteres
        
        $this->shouldThrowAndAssert(
            function() use ($longName) { 
                IndicatorType::at($longName); 
            },
            RuntimeException::class,
            function($exception) {
                $this->assertEquals(IndicatorType::$ERROR_NAME_MAX_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_indicator_type_name_with_minimum_length_is_valid()
    {
        $indicatorType = IndicatorType::at("AB");
        
        $this->assertEquals("AB", $indicatorType->getName());
    }

    public function test_indicator_type_name_with_maximum_length_is_valid()
    {
        $maxName = str_repeat("A", 100); // 100 caracteres exactos
        $indicatorType = IndicatorType::at($maxName);
        
        $this->assertEquals($maxName, $indicatorType->getName());
    }

    public function test_indicator_type_name_gets_trimmed()
    {
        $indicatorType = IndicatorType::at("  Tipo Trimmed  ");
        
        $this->assertEquals("Tipo Trimmed", $indicatorType->getName());
    }

    public function test_indicator_type_name_with_unicode_characters_is_valid()
    {
        $indicatorType = IndicatorType::at("Tipo Porcentual");
        
        $this->assertEquals("Tipo Porcentual", $indicatorType->getName());
    }
}