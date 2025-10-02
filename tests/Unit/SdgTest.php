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
                $this->assertEquals(Sdg::$ERROR_IMAGE_EMPTY, $exception->getMessage());
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
                $this->assertEquals(Sdg::$ERROR_IMAGE_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_sdg_image_must_have_valid_extension()
    {
        $this->shouldThrowAndAssert(
            function () {
                Sdg::at("imagen.txt");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Sdg::$ERROR_INVALID_IMAGE_FILE, $exception->getMessage());
            }
        );
    }

    public function test_sdg_accepts_flexible_naming()
    {
        $flexibleNames = [
            "no-poverty.jpg",
            "objetivo_desarrollo_sostenible_1.png",
            "custom-sustainability-goal.svg",
            "sdg18.png",
            "clean-water-sanitation.webp"
        ];
        
        foreach ($flexibleNames as $name) {
            $sdg = Sdg::at($name);
            $this->assertEquals($name, $sdg->getImage());
            $this->assertInstanceOf(Sdg::class, $sdg);
        }
    }

    public function test_sdg_with_various_valid_extensions()
    {
        $extensions = ["jpg", "jpeg", "png", "gif", "webp", "svg"];
        
        foreach ($extensions as $ext) {
            $sdg = Sdg::at("any-name.{$ext}");
            $this->assertEquals("any-name.{$ext}", $sdg->getImage());
            $this->assertInstanceOf(Sdg::class, $sdg);
        }
    }

    public function test_sdg_with_path_is_valid()
    {
        $sdg = Sdg::at("images/sdgs/sustainability-goal.png");

        $this->assertEquals("images/sdgs/sustainability-goal.png", $sdg->getImage());
        $this->assertInstanceOf(Sdg::class, $sdg);
    }

    public function test_sdg_trims_whitespace()
    {
        $sdg = Sdg::at("  clean-energy.jpg  ");
        
        $this->assertEquals("clean-energy.jpg", $sdg->getImage());
        $this->assertInstanceOf(Sdg::class, $sdg);
    }

    public function test_invalid_file_extensions_throw_error()
    {
        $invalidExtensions = ["txt", "doc", "pdf", "mp4", "exe"];
        
        foreach ($invalidExtensions as $ext) {
            $this->shouldThrowAndAssert(
                function () use ($ext) {
                    Sdg::at("file.{$ext}");
                },
                RuntimeException::class,
                function ($exception) {
                    $this->assertEquals(Sdg::$ERROR_INVALID_IMAGE_FILE, $exception->getMessage());
                }
            );
        }
    }

    public function test_image_name_too_long_throws_error()
    {
        $longName = str_repeat("a", 300) . ".jpg";
        
        $this->shouldThrowAndAssert(
            function () use ($longName) {
                Sdg::at($longName);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Sdg::$ERROR_IMAGE_NAME_TOO_LONG, $exception->getMessage());
            }
        );
    }

    public function test_image_name_with_dangerous_characters_throws_error()
    {
        $dangerousNames = [
            "image<script>.jpg",
            "file>danger.png",
            "bad:name.gif",
            "quote\"file.jpg",
            "pipe|name.png",
            "question?mark.jpg",
            "asterisk*file.png",
            "back\\slash.jpg"
        ];
        
        foreach ($dangerousNames as $name) {
            $this->shouldThrowAndAssert(
                function () use ($name) {
                    Sdg::at($name);
                },
                RuntimeException::class,
                function ($exception) {
                    $this->assertEquals(Sdg::$ERROR_INVALID_CHARACTERS, $exception->getMessage());
                }
            );
        }
    }

    public function test_image_name_with_valid_special_characters()
    {
        $validNames = [
            "goal-1-no-poverty.jpg",
            "sustainable_development.png",
            "clean.water.sanitation.gif",
            "goal (1) - no poverty.jpg",
            "sdg #1 poverty.png"
        ];
        
        foreach ($validNames as $name) {
            $sdg = Sdg::at($name);
            $this->assertEquals($name, $sdg->getImage());
            $this->assertInstanceOf(Sdg::class, $sdg);
        }
    }

}