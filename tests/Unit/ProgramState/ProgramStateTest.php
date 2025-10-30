<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Modules\ProgramState\Domain\ProgramState;
use RuntimeException;
use Exception;

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

    public function test_program_state_can_be_created_with_valid_name()
    {
        $programState = ProgramState::at("EN PROCESO");
        $this->assertEquals("EN PROCESO", $programState->getName());
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
                $this->assertEquals(ProgramState::$ERROR_NAME_EMPTY, $exception->getMessage());
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
                $this->assertEquals(ProgramState::$ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_program_state_must_have_min_length()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProgramState::at("A");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(ProgramState::$ERROR_NAME_MIN_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_program_state_can_be_created_with_any_valid_name()
    {
        $names = ["ACTIVE", "INACTIVE", "COMPLETE", "EN PROCESO", "SUSPENDED", "FINALIZADO"];
        foreach ($names as $name) {
            $programState = ProgramState::at($name);
            $this->assertEquals($name, $programState->getName());
            $this->assertInstanceOf(ProgramState::class, $programState);
        }
    }

    public function test_program_state_with_minimum_length_is_valid()
    {
        $programState = ProgramState::at("ON");
        $this->assertEquals("ON", $programState->getName());
        $this->assertTrue($programState->validateName());
        $this->assertInstanceOf(ProgramState::class, $programState);
    }

    public function test_program_state_with_long_name_is_valid()
    {
        $longName = "Estado de Programa en Ejecución Internacional";
        $programState = ProgramState::at($longName);
        $this->assertEquals($longName, $programState->getName());
        $this->assertTrue($programState->validateName());
        $this->assertInstanceOf(ProgramState::class, $programState);
    }

    public function test_program_state_with_special_characters_is_valid()
    {
        $programState = ProgramState::at("En revisión & Aprobado");
        $this->assertEquals("En revisión & Aprobado", $programState->getName());
        $this->assertTrue($programState->validateName());
        $this->assertInstanceOf(ProgramState::class, $programState);
    }

    public function test_program_state_with_numbers_is_valid()
    {
        $programState = ProgramState::at("Fase 2");
        $this->assertEquals("Fase 2", $programState->getName());
        $this->assertTrue($programState->validateName());
        $this->assertInstanceOf(ProgramState::class, $programState);
    }

    public function test_program_state_with_unicode_characters_is_valid()
    {
        $programState = ProgramState::at("Finalización María José");
        $this->assertEquals("Finalización María José", $programState->getName());
        $this->assertTrue($programState->validateName());
        $this->assertInstanceOf(ProgramState::class, $programState);
    }
}