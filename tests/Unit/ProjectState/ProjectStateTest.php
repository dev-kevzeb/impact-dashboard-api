<?php

namespace Tests\Unit\ProjectState;

use Tests\TestCase;
use App\Modules\ProjectState\Domain\ProjectState;
use RuntimeException;

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

    public function test_project_state_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectState::at("");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(ProjectState::$ERROR_STATE_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_project_state_name_cannot_be_only_spaces()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectState::at("   ");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(ProjectState::$ERROR_STATE_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_project_state_name_too_short_throws_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectState::at("A");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(ProjectState::$ERROR_STATE_MIN_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_project_state_name_two_characters_throws_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectState::at("AB");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(ProjectState::$ERROR_STATE_MIN_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_project_state_can_be_created_with_valid_data()
    {
        $projectState = ProjectState::at("ACTIVE");
        
        $this->assertInstanceOf(ProjectState::class, $projectState);
        $this->assertEquals("ACTIVE", $projectState->getState());
    }

    public function test_project_state_name_with_minimum_length_is_valid()
    {
        $projectState = ProjectState::at("ABC");
        
        $this->assertInstanceOf(ProjectState::class, $projectState);
        $this->assertEquals("ABC", $projectState->getState());
    }

    public function test_project_state_with_numeric_string_is_valid()
    {
        $projectState = ProjectState::at("123");
        
        $this->assertInstanceOf(ProjectState::class, $projectState);
        $this->assertEquals("123", $projectState->getState());
    }

    public function test_project_state_with_spaces_is_valid()
    {
        $projectState = ProjectState::at("En Proceso");
        
        $this->assertInstanceOf(ProjectState::class, $projectState);
        $this->assertEquals("En Proceso", $projectState->getState());
    }

    public function test_project_state_with_special_characters_is_valid()
    {
        $projectState = ProjectState::at("En-Proceso");
        
        $this->assertInstanceOf(ProjectState::class, $projectState);
        $this->assertEquals("En-Proceso", $projectState->getState());
    }

    public function test_project_state_with_unicode_characters_is_valid()
    {
        $projectState = ProjectState::at("Ejecución");
        
        $this->assertInstanceOf(ProjectState::class, $projectState);
        $this->assertEquals("Ejecución", $projectState->getState());
    }

    public function test_project_state_name_too_long_throws_exception()
    {
        $longName = str_repeat("A", 101); // 101 caracteres
        
        $this->shouldThrowAndAssert(
            function () use ($longName) {
                ProjectState::at($longName);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(ProjectState::$ERROR_STATE_MAX_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_project_state_name_gets_trimmed()
    {
        $projectState = ProjectState::at("  Estado Trimmed  ");
        
        $this->assertEquals("Estado Trimmed", $projectState->getState());
    }
}