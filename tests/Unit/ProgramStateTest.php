<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\ProgramState;
use Exception;
use RuntimeException;

class ProgramStateTest extends TestCase
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

    public function test_program_state_can_be_created_with_active_state()
    {
        $programState = ProgramState::at("ACTIVE");

        $this->assertEquals("ACTIVE", $programState->getState());
        $this->assertTrue($programState->isActive());
        $this->assertFalse($programState->isInactive());
        $this->assertFalse($programState->isComplete());
        $this->assertInstanceOf(ProgramState::class, $programState);
    }

    public function test_program_state_can_be_created_with_inactive_state()
    {
        $programState = ProgramState::at("INACTIVE");

        $this->assertEquals("INACTIVE", $programState->getState());
        $this->assertFalse($programState->isActive());
        $this->assertTrue($programState->isInactive());
        $this->assertFalse($programState->isComplete());
        $this->assertInstanceOf(ProgramState::class, $programState);
    }

    public function test_program_state_can_be_created_with_complete_state()
    {
        $programState = ProgramState::at("COMPLETE");

        $this->assertEquals("COMPLETE", $programState->getState());
        $this->assertFalse($programState->isActive());
        $this->assertFalse($programState->isInactive());
        $this->assertTrue($programState->isComplete());
        $this->assertInstanceOf(ProgramState::class, $programState);
    }

    public function test_program_state_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProgramState::at("");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(ProgramState::$ERROR_STATE_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_program_state_cannot_be_only_spaces()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProgramState::at("   ");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(ProgramState::$ERROR_STATE_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_program_state_must_be_valid_state()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProgramState::at("INVALID");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(ProgramState::$ERROR_STATE_INVALID, $exception->getMessage());
            }
        );
    }

    public function test_program_state_with_lowercase_throws_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProgramState::at("active");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(ProgramState::$ERROR_STATE_INVALID, $exception->getMessage());
            }
        );
    }

    public function test_program_state_with_mixed_case_throws_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProgramState::at("Active");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(ProgramState::$ERROR_STATE_INVALID, $exception->getMessage());
            }
        );
    }



    public function test_all_valid_states_work_correctly()
    {
        $validStates = [
            'ACTIVE' => ['isActive' => true, 'isInactive' => false, 'isComplete' => false],
            'INACTIVE' => ['isActive' => false, 'isInactive' => true, 'isComplete' => false],
            'COMPLETE' => ['isActive' => false, 'isInactive' => false, 'isComplete' => true]
        ];

        foreach ($validStates as $stateName => $expectedBooleans) {
            $programState = ProgramState::at($stateName);
            
            $this->assertEquals($stateName, $programState->getState());
            $this->assertEquals($expectedBooleans['isActive'], $programState->isActive());
            $this->assertEquals($expectedBooleans['isInactive'], $programState->isInactive());
            $this->assertEquals($expectedBooleans['isComplete'], $programState->isComplete());
        }
    }
}