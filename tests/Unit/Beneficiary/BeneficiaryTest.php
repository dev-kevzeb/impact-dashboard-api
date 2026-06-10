<?php

namespace Tests\Unit\Beneficiary;

use Exception;
use PHPUnit\Framework\TestCase;
use App\Modules\Beneficiary\Domain\Beneficiary;
use RuntimeException;

class BeneficiaryTest extends TestCase
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

    public function test_beneficiary_can_be_created_with_valid_name()
    {
        $beneficiary = Beneficiary::at("Juan Pérez");

        $this->assertEquals("Juan Pérez", $beneficiary->getName());
        $this->assertEquals("Juan Pérez", $beneficiary->name);
        $this->assertInstanceOf(Beneficiary::class, $beneficiary);
    }

    public function test_beneficiary_can_be_created_with_minimum_length()
    {
        $beneficiary = Beneficiary::at("AB");

        $this->assertEquals("AB", $beneficiary->getName());
        $this->assertInstanceOf(Beneficiary::class, $beneficiary);
    }

    public function test_beneficiary_trims_whitespace()
    {
        $beneficiary = Beneficiary::at("  María García  ");

        $this->assertEquals("María García", $beneficiary->getName());
        $this->assertInstanceOf(Beneficiary::class, $beneficiary);
    }

    public function test_beneficiary_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Beneficiary::at("");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Beneficiary::$ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_beneficiary_cannot_be_only_spaces()
    {
        $this->shouldThrowAndAssert(
            function () {
                Beneficiary::at("   ");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Beneficiary::$ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_beneficiary_cannot_be_too_short()
    {
        $this->shouldThrowAndAssert(
            function () {
                Beneficiary::at("A");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Beneficiary::$ERROR_NAME_MIN_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_beneficiary_cannot_be_too_long()
    {
        $longName = str_repeat("A", 256);

        $this->shouldThrowAndAssert(
            function () use ($longName) {
                Beneficiary::at($longName);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Beneficiary::$ERROR_NAME_TOO_LONG, $exception->getMessage());
            }
        );
    }

    public function test_beneficiary_can_handle_maximum_length()
    {
        $maxName = str_repeat("A", 255); // Exactamente 255 caracteres

        $beneficiary = Beneficiary::at($maxName);
        
        $this->assertEquals($maxName, $beneficiary->getName());
        $this->assertInstanceOf(Beneficiary::class, $beneficiary);
    }

    public function test_beneficiary_handles_special_characters()
    {
        $specialName = "José María O'Connor-Smith";

        $beneficiary = Beneficiary::at($specialName);
        
        $this->assertEquals($specialName, $beneficiary->getName());
        $this->assertInstanceOf(Beneficiary::class, $beneficiary);
    }

    public function test_beneficiary_handles_unicode_characters()
    {
        $unicodeName = "李小明 José Müller";

        $beneficiary = Beneficiary::at($unicodeName);
        
        $this->assertEquals($unicodeName, $beneficiary->getName());
        $this->assertInstanceOf(Beneficiary::class, $beneficiary);
    }

    public function test_all_valid_beneficiary_names_work_correctly()
    {
        $validNames = [
            "Juan Pérez",
            "María García López",
            "Carlos Alberto",
            "Ana-Sofía Rodríguez",
            "José María O'Connor",
            "李小明",
            "Dr. Roberto Silva Jr.",
            "Fundación Educativa Nacional"
        ];

        foreach ($validNames as $name) {
            $beneficiary = Beneficiary::at($name);
            
            $this->assertEquals($name, $beneficiary->getName());
            $this->assertInstanceOf(Beneficiary::class, $beneficiary);
        }
    }
}