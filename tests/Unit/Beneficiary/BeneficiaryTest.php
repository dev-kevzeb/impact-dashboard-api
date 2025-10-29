<?php

namespace Tests\Unit\Beneficiary;

use PHPUnit\Framework\TestCase;
use App\Modules\Beneficiary\Domain\Beneficiary;
use InvalidArgumentException;

class BeneficiaryTest extends TestCase
{
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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El nombre del beneficiario no puede estar vacío');

        Beneficiary::at("");
    }

    public function test_beneficiary_cannot_be_only_spaces()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El nombre del beneficiario no puede estar vacío');

        Beneficiary::at("   ");
    }

    public function test_beneficiary_cannot_be_too_short()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El nombre del beneficiario debe tener al menos 2 caracteres');

        Beneficiary::at("A");
    }

    public function test_beneficiary_cannot_be_too_long()
    {
        $longName = str_repeat("A", 256); // 256 caracteres

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El nombre del beneficiario no puede exceder 255 caracteres');

        Beneficiary::at($longName);
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