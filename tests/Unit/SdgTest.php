<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Sdg;
use Exception;
use RuntimeException;

class SdgTest extends TestCase
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

    public function test_sdg_can_be_created_with_valid_image()
    {
        $sdg = Sdg::at("sdg1.png");

        $this->assertEquals("sdg1.png", $sdg->getImage());
        $this->assertEquals(1, $sdg->getNumber());
        $this->assertTrue($sdg->validateImage());
        $this->assertTrue($sdg->isValidSdgNumber());
        $this->assertInstanceOf(Sdg::class, $sdg);
    }

    public function test_sdg_image_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Sdg::at("");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la imagen del SDG no debe ir vacio", $exception->getMessage());
            }
        );
    }

    public function test_sdg_image_cannot_be_only_spaces()
    {
        $this->shouldThrowAndAssert(
            function () {
                Sdg::at("   ");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la imagen del SDG no debe ir vacio", $exception->getMessage());
            }
        );
    }

    public function test_sdg_image_must_contain_valid_number()
    {
        $this->shouldThrowAndAssert(
            function () {
                Sdg::at("imagen.png");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la imagen del SDG debe contener un número válido (ej: sdg1.png)", $exception->getMessage());
            }
        );
    }

    public function test_sdg_number_must_be_between_1_and_17()
    {
        $this->shouldThrowAndAssert(
            function () {
                Sdg::at("sdg0.png");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el número del SDG debe estar entre 1 y 17", $exception->getMessage());
            }
        );
    }

    public function test_sdg_number_cannot_exceed_17()
    {
        $this->shouldThrowAndAssert(
            function () {
                Sdg::at("sdg18.png");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el número del SDG debe estar entre 1 y 17", $exception->getMessage());
            }
        );
    }

    public function test_sdg_with_all_valid_numbers()
    {
        for ($i = 1; $i <= 17; $i++) {
            $sdg = Sdg::at("sdg{$i}.png");
            
            $this->assertEquals("sdg{$i}.png", $sdg->getImage());
            $this->assertEquals($i, $sdg->getNumber());
            $this->assertTrue($sdg->isValidSdgNumber());
        }
    }

    public function test_sdg_with_uppercase_format_is_valid()
    {
        $sdg = Sdg::at("SDG5.png");

        $this->assertEquals("SDG5.png", $sdg->getImage());
        $this->assertEquals(5, $sdg->getNumber());
        $this->assertTrue($sdg->isValidSdgNumber());
        $this->assertInstanceOf(Sdg::class, $sdg);
    }

    public function test_sdg_with_different_extensions_is_valid()
    {
        $extensions = ["jpg", "jpeg", "png", "gif", "webp"];
        
        foreach ($extensions as $ext) {
            $sdg = Sdg::at("sdg3.{$ext}");
            
            $this->assertEquals("sdg3.{$ext}", $sdg->getImage());
            $this->assertEquals(3, $sdg->getNumber());
            $this->assertTrue($sdg->isValidSdgNumber());
        }
    }

    public function test_sdg_with_path_is_valid()
    {
        $sdg = Sdg::at("images/sdgs/sdg7.png");

        $this->assertEquals("images/sdgs/sdg7.png", $sdg->getImage());
        $this->assertEquals(7, $sdg->getNumber());
        $this->assertTrue($sdg->isValidSdgNumber());
        $this->assertInstanceOf(Sdg::class, $sdg);
    }

    public function test_sdg_with_double_digit_numbers()
    {
        $doubleDigits = [10, 11, 12, 13, 14, 15, 16, 17];
        
        foreach ($doubleDigits as $number) {
            $sdg = Sdg::at("sdg{$number}.png");
            
            $this->assertEquals("sdg{$number}.png", $sdg->getImage());
            $this->assertEquals($number, $sdg->getNumber());
            $this->assertTrue($sdg->isValidSdgNumber());
        }
    }

}