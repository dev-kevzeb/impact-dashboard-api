<?php

namespace Tests\Unit\Sdg;

use PHPUnit\Framework\TestCase;
use App\Modules\Sdg\Domain\Sdg;
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
        $sdg = Sdg::at("sdg1.png", "sdg1.png");

        $this->assertEquals("sdg1.png", $sdg->getImage());
        $this->assertEquals("sdg1.png", $sdg->filename);
        $this->assertInstanceOf(Sdg::class, $sdg);
    }

    public function test_sdg_image_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Sdg::at("", "valid.png");
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
                Sdg::at("   ", "valid.png");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Sdg::$ERROR_IMAGE_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_sdg_image_must_have_extension()
    {
        $this->shouldThrowAndAssert(
            function () {
                Sdg::at("imagen", "valid.png");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Sdg::$ERROR_MISSING_EXTENSION, $exception->getMessage());
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
            $sdg = Sdg::at($name, $name);
            $this->assertEquals($name, $sdg->getImage());
            $this->assertEquals($name, $sdg->filename);
            $this->assertInstanceOf(Sdg::class, $sdg);
        }
    }

    public function test_sdg_accepts_files_with_any_extension()
    {
        $extensions = ["jpg", "jpeg", "png", "gif", "webp", "svg", "txt", "pdf"];
        
        foreach ($extensions as $ext) {
            $sdg = Sdg::at("any-name.{$ext}", "any-name.{$ext}");
            $this->assertEquals("any-name.{$ext}", $sdg->getImage());
            $this->assertEquals("any-name.{$ext}", $sdg->filename);
            $this->assertInstanceOf(Sdg::class, $sdg);
        }
    }

    public function test_sdg_with_path_is_valid()
    {
        $sdg = Sdg::at("images/sdgs/sustainability-goal.png", "sustainability-goal.png");

        $this->assertEquals("images/sdgs/sustainability-goal.png", $sdg->getImage());
        $this->assertEquals("sustainability-goal.png", $sdg->filename);
        $this->assertInstanceOf(Sdg::class, $sdg);
    }

    public function test_sdg_trims_whitespace()
    {
        $sdg = Sdg::at("  clean-energy.jpg  ", "  clean-energy.jpg  ");
        
        $this->assertEquals("clean-energy.jpg", $sdg->getImage());
        $this->assertEquals("clean-energy.jpg", $sdg->filename);
        $this->assertInstanceOf(Sdg::class, $sdg);
    }



    public function test_image_name_too_long_throws_error()
    {
        $longName = str_repeat("a", 300) . ".jpg";
        
        $this->shouldThrowAndAssert(
            function () use ($longName) {
                Sdg::at($longName, "valid.png");
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
                    Sdg::at($name, "valid.png");
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
            $sdg = Sdg::at($name, $name);
            $this->assertEquals($name, $sdg->getImage());
            $this->assertEquals($name, $sdg->filename);
            $this->assertInstanceOf(Sdg::class, $sdg);
        }
    }

    public function test_image_without_extension_throws_specific_error()
    {
        $namesWithoutExtension = ["imagen", "sdg1", "no-poverty", "sustainability"];

        foreach ($namesWithoutExtension as $name) {
            $this->shouldThrowAndAssert(
                function () use ($name) {
                    Sdg::at($name, "valid.png");
                },
                RuntimeException::class,
                function ($exception) {
                    $this->assertEquals(Sdg::$ERROR_MISSING_EXTENSION, $exception->getMessage());
                }
            );
        }
    }

    public function test_image_with_only_extension_throws_specific_error()
    {
        $onlyExtensions = [".jpg", ".png", ".gif", "  .svg  ", "\t.webp"];

        foreach ($onlyExtensions as $name) {
            $this->shouldThrowAndAssert(
                function () use ($name) {
                    Sdg::at($name, "valid.png");
                },
                RuntimeException::class,
                function ($exception) {
                    $this->assertEquals(Sdg::$ERROR_MISSING_FILENAME, $exception->getMessage());
                }
            );
        }
    }

    public function test_error_messages_are_specific_and_clear()
    {
        $testCases = [
            [".jpg", Sdg::$ERROR_MISSING_FILENAME, "solo extensión"],
            ["imagen", Sdg::$ERROR_MISSING_EXTENSION, "sin extensión"],
            ["", Sdg::$ERROR_IMAGE_EMPTY, "completamente vacío"],
            ["   ", Sdg::$ERROR_IMAGE_EMPTY, "solo espacios"]
        ];

        foreach ($testCases as [$input, $expectedError, $description]) {
            $this->shouldThrowAndAssert(
                function () use ($input) {
                    Sdg::at($input, "valid.png");
                },
                RuntimeException::class,
                function ($exception) use ($expectedError, $description, $input) {
                    $this->assertEquals($expectedError, $exception->getMessage(),
                        "Error incorrecto para {$description} ('{$input}')");
                }
            );
        }
    }

    public function test_filename_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Sdg::at("valid.png", "");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Sdg::$ERROR_IMAGE_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_filename_cannot_be_only_spaces()
    {
        $this->shouldThrowAndAssert(
            function () {
                Sdg::at("valid.png", "   ");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Sdg::$ERROR_IMAGE_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_filename_too_long_throws_error()
    {
        $longName = str_repeat("a", 300);
        
        $this->shouldThrowAndAssert(
            function () use ($longName) {
                Sdg::at("valid.png", $longName);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Sdg::$ERROR_IMAGE_NAME_TOO_LONG, $exception->getMessage());
            }
        );
    }

    public function test_filename_with_dangerous_characters_throws_error()
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
                    Sdg::at("valid.png", $name);
                },
                RuntimeException::class,
                function ($exception) {
                    $this->assertEquals(Sdg::$ERROR_INVALID_CHARACTERS, $exception->getMessage());
                }
            );
        }
    }

    public function test_filename_with_valid_special_characters()
    {
        $validNames = [
            "goal-1-no-poverty.jpg",
            "sustainable_development.png",
            "clean.water.sanitation.gif",
            "goal (1) - no poverty.jpg",
            "sdg #1 poverty.png"
        ];
        
        foreach ($validNames as $name) {
            $sdg = Sdg::at("valid.png", $name);
            $this->assertEquals("valid.png", $sdg->getImage());
            $this->assertEquals($name, $sdg->filename);
            $this->assertInstanceOf(Sdg::class, $sdg);
        }
    }

    public function test_filename_trims_whitespace()
    {
        $sdg = Sdg::at("valid.png", "  clean-energy.jpg  ");
        
        $this->assertEquals("valid.png", $sdg->getImage());
        $this->assertEquals("clean-energy.jpg", $sdg->filename);
        $this->assertInstanceOf(Sdg::class, $sdg);
    }

    public function test_filename_must_have_extension()
    {
        $this->shouldThrowAndAssert(
            function () {
                Sdg::at("valid.png", "filename_without_extension");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Sdg::$ERROR_MISSING_EXTENSION, $exception->getMessage());
            }
        );
    }

    public function test_filename_with_only_extension_throws_error()
    {
        $this->shouldThrowAndAssert(
            function () {
                Sdg::at("valid.png", ".jpg");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Sdg::$ERROR_MISSING_FILENAME, $exception->getMessage());
            }
        );
    }

}