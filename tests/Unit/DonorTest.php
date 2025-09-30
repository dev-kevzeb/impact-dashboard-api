<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Donor;
use Exception;
use RuntimeException;

class DonorTest extends TestCase
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

    public function test_donor_can_be_created_with_valid_name()
    {
        $donor = Donor::at("World Bank");

        $this->assertEquals("World Bank", $donor->getName());
        $this->assertTrue($donor->validateName());
        $this->assertInstanceOf(Donor::class, $donor);
    }

    public function test_donor_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Donor::at("");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Donor::ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_donor_name_cannot_be_only_spaces()
    {
        $this->shouldThrowAndAssert(
            function () {
                Donor::at("   ");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Donor::ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_donor_name_too_short_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                Donor::at("A");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Donor::ERROR_NAME_MIN_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_donor_name_with_minimum_length_is_valid()
    {
        $donor = Donor::at("UN");

        $this->assertEquals("UN", $donor->getName());
        $this->assertTrue($donor->validateName());
        $this->assertInstanceOf(Donor::class, $donor);
    }

    public function test_donor_name_with_long_name_is_valid()
    {
        $longName = "International Bank for Reconstruction and Development";
        $donor = Donor::at($longName);

        $this->assertEquals($longName, $donor->getName());
        $this->assertTrue($donor->validateName());
        $this->assertInstanceOf(Donor::class, $donor);
    }

    public function test_donor_name_with_special_characters_is_valid()
    {
        $donor = Donor::at("Bill & Melinda Gates Foundation");

        $this->assertEquals("Bill & Melinda Gates Foundation", $donor->getName());
        $this->assertTrue($donor->validateName());
        $this->assertInstanceOf(Donor::class, $donor);
    }

    public function test_donor_name_with_numbers_is_valid()
    {
        $donor = Donor::at("G7 Development Fund");

        $this->assertEquals("G7 Development Fund", $donor->getName());
        $this->assertTrue($donor->validateName());
        $this->assertInstanceOf(Donor::class, $donor);
    }

    public function test_donor_name_with_unicode_characters_is_valid()
    {
        $donor = Donor::at("Fundación María José");

        $this->assertEquals("Fundación María José", $donor->getName());
        $this->assertTrue($donor->validateName());
        $this->assertInstanceOf(Donor::class, $donor);
    }

}