<?php

namespace Tests\Unit;

use App\Models\ProjectState;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class ProjectStateTest extends TestCase
{
    public function shouldThrowAndAssert($should, $exceptionType, $assertions)
    {
        try {
            $should->__invoke();
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $exception) {
            $this->assertEquals($exceptionType, get_class($exception));
            $assertions->__invoke($exception);
        }
    }

    
    public function test_state_with_integer_throws_invalid_argument_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectState::at(123);
            },
            InvalidArgumentException::class,
            function ($exception) {
                $this->assertEquals('El estado del proyecto no es valido', $exception->getMessage());
            }
        );
    }

    public function test_state_with_float_throws_invalid_argument_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectState::at(123.45);
            },
            InvalidArgumentException::class,
            function ($exception) {
                $this->assertEquals('El estado del proyecto no es valido', $exception->getMessage());
            }
        );
    }

    public function test_state_with_boolean_throws_invalid_argument_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectState::at(true);
            },
            InvalidArgumentException::class,
            function ($exception) {
                $this->assertEquals('El estado del proyecto no es valido', $exception->getMessage());
            }
        );
    }

    public function test_state_with_array_throws_invalid_argument_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectState::at(['ACTIVE']);
            },
            InvalidArgumentException::class,
            function ($exception) {
                $this->assertEquals('El estado del proyecto no es valido', $exception->getMessage());
            }
        );
    }

    public function test_state_with_object_throws_invalid_argument_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectState::at(new \stdClass());
            },
            InvalidArgumentException::class,
            function ($exception) {
                $this->assertEquals('El estado del proyecto no es valido', $exception->getMessage());
            }
        );
    }

    public function test_state_with_null_throws_invalid_argument_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectState::at(null);
            },
            InvalidArgumentException::class,
            function ($exception) {
                $this->assertEquals('El estado del proyecto no es valido', $exception->getMessage());
            }
        );
    }


    public function test_state_with_empty_string_throws_invalid_argument_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectState::at("");
            },
            InvalidArgumentException::class,
            function ($exception) {
                $this->assertEquals('el nombre del estado del proyecto no debe ser null o menor a 3 caracteres', $exception->getMessage());
            }
        );
    }

    public function test_state_with_one_character_throws_invalid_argument_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectState::at("A");
            },
            InvalidArgumentException::class,
            function ($exception) {
                $this->assertEquals('el nombre del estado del proyecto no debe ser null o menor a 3 caracteres', $exception->getMessage());
            }
        );
    }

    public function test_state_with_two_characters_throws_invalid_argument_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectState::at("AB");
            },
            InvalidArgumentException::class,
            function ($exception) {
                $this->assertEquals('el nombre del estado del proyecto no debe ser null o menor a 3 caracteres', $exception->getMessage());
            }
        );
    }

    public function test_state_with_only_whitespace_throws_invalid_argument_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectState::at("   ");
            },
            InvalidArgumentException::class,
            function ($exception) {
                $this->assertEquals('el nombre del estado del proyecto no debe ser null o menor a 3 caracteres', $exception->getMessage());
            }
        );
    }


    public function test_state_with_numeric_string_throws_invalid_argument_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectState::at("123");
            },
            InvalidArgumentException::class,
            function ($exception) {
                $this->assertEquals('El estado del proyecto no es valido', $exception->getMessage());
            }
        );
    }

    public function test_state_with_numeric_string_longer_throws_invalid_argument_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectState::at("12345");
            },
            InvalidArgumentException::class,
            function ($exception) {
                $this->assertEquals('El estado del proyecto no es valido', $exception->getMessage());
            }
        );
    }
    public function test_state_with_exactly_three_characters_succeeds()
    {
        $projectState = ProjectState::at("ABC");
        
        $this->assertInstanceOf(ProjectState::class, $projectState);
        $this->assertEquals("ABC", $projectState->getName());
    }

    public function test_state_with_valid_name_succeeds()
    {
        $projectState = ProjectState::at("ACTIVE");
        
        $this->assertInstanceOf(ProjectState::class, $projectState);
        $this->assertEquals("ACTIVE", $projectState->getName());
    }

    public function test_state_with_long_valid_name_succeeds()
    {
        $projectState = ProjectState::at("En ejecucion");
        
        $this->assertInstanceOf(ProjectState::class, $projectState);
        $this->assertEquals("En ejecucion", $projectState->getName());
    }

    public function test_state_with_mixed_case_succeeds()
    {
        $projectState = ProjectState::at("Pending");
        
        $this->assertInstanceOf(ProjectState::class, $projectState);
        $this->assertEquals("Pending", $projectState->getName());
    }

    public function test_state_with_spaces_succeeds()
    {
        $projectState = ProjectState::at("En Proceso");
        
        $this->assertInstanceOf(ProjectState::class, $projectState);
        $this->assertEquals("En Proceso", $projectState->getName());
    }

    public function test_state_with_special_characters_succeeds()
    {
        $projectState = ProjectState::at("En-Proceso");
        $this->assertInstanceOf(ProjectState::class, $projectState);
        $this->assertEquals("En-Proceso", $projectState->getName());
    }

    public function test_state_with_accents_succeeds()
    {
        $projectState = ProjectState::at("Ejecucion");
        $this->assertInstanceOf(ProjectState::class, $projectState);
        $this->assertEquals("Ejecucion", $projectState->getName());
    }


    public function test_is_string_returns_true_for_string()
    {
        $this->assertTrue(ProjectState::isString("test"));
    }

    public function test_is_string_returns_false_for_integer()
    {
        $this->assertFalse(ProjectState::isString(123));
    }

    public function test_is_string_returns_false_for_null()
    {
        $this->assertFalse(ProjectState::isString(null));
    }

    public function test_is_string_returns_false_for_boolean()
    {
        $this->assertFalse(ProjectState::isString(true));
    }

}