<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Beneficiary;
use Exception;
use RuntimeException;

class BeneficiaryTest extends TestCase
{
    // Closure para manejo de errores
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

    public function test_program_beneficiary_can_be_created_with_government()
    {
        $beneficiary = Beneficiary::at("GOVERNMENT");

        $this->assertEquals("GOVERNMENT", $beneficiary->getName());
        $this->assertTrue($beneficiary->validateName());
        $this->assertTrue($beneficiary->isGovernment());
        $this->assertFalse($beneficiary->isPrivateSector());
        $this->assertFalse($beneficiary->isGovernmentAndPrivateSector());
        $this->assertInstanceOf(Beneficiary::class, $beneficiary);
    }

    public function test_program_beneficiary_can_be_created_with_private_sector()
    {
        $beneficiary = Beneficiary::at("PRIVATE_SECTOR");

        $this->assertEquals("PRIVATE_SECTOR", $beneficiary->getName());
        $this->assertTrue($beneficiary->validateName());
        $this->assertFalse($beneficiary->isGovernment());
        $this->assertTrue($beneficiary->isPrivateSector());
        $this->assertFalse($beneficiary->isGovernmentAndPrivateSector());
        $this->assertInstanceOf(Beneficiary::class, $beneficiary);
    }

    public function test_program_beneficiary_can_be_created_with_government_and_private_sector()
    {
        $beneficiary = Beneficiary::at("GOVERNMENT_AND_PRIVATE_SECTOR");

        $this->assertEquals("GOVERNMENT_AND_PRIVATE_SECTOR", $beneficiary->getName());
        $this->assertTrue($beneficiary->validateName());
        $this->assertFalse($beneficiary->isGovernment());
        $this->assertFalse($beneficiary->isPrivateSector());
        $this->assertTrue($beneficiary->isGovernmentAndPrivateSector());
        $this->assertInstanceOf(Beneficiary::class, $beneficiary);
    }

    public function test_program_beneficiary_cannot_be_empty()
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

    public function test_program_beneficiary_cannot_be_only_spaces()
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

    public function test_program_beneficiary_must_be_valid_type()
    {
        $this->shouldThrowAndAssert(
            function () {
                Beneficiary::at("INVALID");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Beneficiary::$ERROR_NAME_INVALID, $exception->getMessage());
            }
        );
    }

    public function test_program_beneficiary_with_lowercase_throws_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                Beneficiary::at("government");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Beneficiary::$ERROR_NAME_INVALID, $exception->getMessage());
            }
        );
    }

    public function test_program_beneficiary_with_mixed_case_throws_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                Beneficiary::at("Government");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Beneficiary::$ERROR_NAME_INVALID, $exception->getMessage());
            }
        );
    }



    public function test_all_valid_beneficiaries_work_correctly()
    {
        $validBeneficiaries = [
            'GOVERNMENT' => ['isGovernment' => true, 'isPrivateSector' => false, 'isGovernmentAndPrivateSector' => false],
            'PRIVATE_SECTOR' => ['isGovernment' => false, 'isPrivateSector' => true, 'isGovernmentAndPrivateSector' => false],
            'GOVERNMENT_AND_PRIVATE_SECTOR' => ['isGovernment' => false, 'isPrivateSector' => false, 'isGovernmentAndPrivateSector' => true]
        ];

        foreach ($validBeneficiaries as $beneficiaryName => $expectedBooleans) {
            $beneficiary = Beneficiary::at($beneficiaryName);
            
            $this->assertEquals($beneficiaryName, $beneficiary->getName());
            $this->assertTrue($beneficiary->validateName());
            $this->assertEquals($expectedBooleans['isGovernment'], $beneficiary->isGovernment());
            $this->assertEquals($expectedBooleans['isPrivateSector'], $beneficiary->isPrivateSector());
            $this->assertEquals($expectedBooleans['isGovernmentAndPrivateSector'], $beneficiary->isGovernmentAndPrivateSector());
        }
    }

    public function test_program_beneficiary_with_spaces_throws_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                Beneficiary::at("GOVERNMENT AND PRIVATE SECTOR");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Beneficiary::$ERROR_NAME_INVALID, $exception->getMessage());
            }
        );
    }
}