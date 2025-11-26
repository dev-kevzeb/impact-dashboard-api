<?php

namespace Tests\Unit\Program;

use PHPUnit\Framework\TestCase;
use App\Modules\Program\Domain\Program;
use App\Modules\ProgramState\Domain\ProgramState;
use App\Modules\Contact\Domain\Contact;
use RuntimeException;


class ProgramTest extends TestCase
{
    private ProgramState $validProgramState;
    private Contact $validContact;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validProgramState = ProgramState::at("Activo");
        $this->validContact = Contact::at("Juan", "Pérez", "Director", "juan@example.com", "+50688888888");
    }

    public function test_can_create_program_with_valid_data(): void
    {
        $program = Program::at(
            "Programa de Educación Rural 2025",
            "Programa enfocado en mejorar la educación en zonas rurales mediante capacitación docente",
            "program_banners/banner.jpg",
            "https://www.programa-educacion.org",
            $this->validContact,
            $this->validProgramState
        );

        $this->assertInstanceOf(Program::class, $program);
        $this->assertEquals("Programa de Educación Rural 2025", $program->name);
        $this->assertEquals("Programa enfocado en mejorar la educación en zonas rurales mediante capacitación docente", $program->description);
        $this->assertEquals("program_banners/banner.jpg", $program->banner_img);
        $this->assertEquals("https://www.programa-educacion.org", $program->program_url);
    }

    public function test_can_create_program_with_minimum_valid_name(): void
    {
        $program = Program::at(
            "ABC",  // 3 caracteres (mínimo)
            "Descripción válida con más de 10 caracteres",
            "banner.jpg",
            "https://example.com",
            $this->validContact,
            $this->validProgramState
        );

        $this->assertEquals("ABC", $program->name);
    }

    public function test_can_create_program_with_minimum_valid_description(): void
    {
        $program = Program::at(
            "Programa Test",
            "1234567890",  // 10 caracteres (mínimo)
            "banner.jpg",
            "",
            $this->validContact,
            $this->validProgramState
        );

        $this->assertEquals("1234567890", $program->description);
    }

    public function test_can_create_program_with_null_banner_img(): void
    {
        $program = Program::at(
            "Programa Sin Banner",
            "Programa de desarrollo sin imagen de banner",
            null,  // banner_img es nullable
            "",
            $this->validContact,
            $this->validProgramState
        );

        $this->assertInstanceOf(Program::class, $program);
        $this->assertNull($program->banner_img);
    }

    public function test_trims_whitespace_from_inputs(): void
    {
        $program = Program::at(
            "  Programa con espacios  ",
            "  Descripción con espacios al inicio y final  ",
            "  banner.jpg  ",
            "  https://example.com  ",
            $this->validContact,
            $this->validProgramState
        );

        $this->assertEquals("Programa con espacios", $program->name);
        $this->assertEquals("Descripción con espacios al inicio y final", $program->description);
        $this->assertEquals("banner.jpg", $program->banner_img);
        $this->assertEquals("https://example.com", $program->program_url);
    }

    public function test_can_create_program_with_empty_url(): void
    {
        $program = Program::at(
            "Programa Test",
            "Descripción válida del programa",
            "banner.jpg",
            "",  // URL vacía es permitida
            $this->validContact,
            $this->validProgramState
        );

        $this->assertEquals("", $program->program_url);
    }

    public function test_throws_exception_when_name_is_empty(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(Program::$ERROR_NAME_EMPTY);

        Program::at(
            "",  // Nombre vacío
            "Descripción válida del programa",
            "banner.jpg",
            "https://example.com",
            $this->validContact,
            $this->validProgramState
        );
    }

    public function test_throws_exception_when_name_is_only_whitespace(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(Program::$ERROR_NAME_EMPTY);

        Program::at(
            "   ",  // Solo espacios
            "Descripción válida del programa",
            "banner.jpg",
            "https://example.com",
            $this->validContact,
            $this->validProgramState
        );
    }

    public function test_throws_exception_when_name_is_too_short(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(Program::$ERROR_NAME_MIN_LENGTH);

        Program::at(
            "AB",  // 2 caracteres (mínimo es 3)
            "Descripción válida del programa",
            "banner.jpg",
            "https://example.com",
            $this->validContact,
            $this->validProgramState
        );
    }

    public function test_throws_exception_when_name_is_too_short_after_trim(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(Program::$ERROR_NAME_MIN_LENGTH);

        Program::at(
            "  A  ",  // 1 carácter después de trim
            "Descripción válida del programa",
            "banner.jpg",
            "https://example.com",
            $this->validContact,
            $this->validProgramState
        );
    }

    public function test_throws_exception_when_description_is_empty(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(Program::$ERROR_DESCRIPTION_EMPTY);

        Program::at(
            "Programa Válido",
            "",  // Descripción vacía
            "banner.jpg",
            "https://example.com",
            $this->validContact,
            $this->validProgramState
        );
    }

    public function test_throws_exception_when_description_is_only_whitespace(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(Program::$ERROR_DESCRIPTION_EMPTY);

        Program::at(
            "Programa Válido",
            "     ",  // Solo espacios
            "banner.jpg",
            "https://example.com",
            $this->validContact,
            $this->validProgramState
        );
    }

    public function test_throws_exception_when_description_is_too_short(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(Program::$ERROR_DESCRIPTION_MIN_LENGTH);

        Program::at(
            "Programa Válido",
            "123456789",  // 9 caracteres (mínimo es 10)
            "banner.jpg",
            "https://example.com",
            $this->validContact,
            $this->validProgramState
        );
    }

    public function test_throws_exception_when_description_is_too_short_after_trim(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(Program::$ERROR_DESCRIPTION_MIN_LENGTH);

        Program::at(
            "Programa Válido",
            "  ABC  ",  // 3 caracteres después de trim (mínimo es 10)
            "banner.jpg",
            "https://example.com",
            $this->validContact,
            $this->validProgramState
        );
    }

    public function test_can_create_program_with_whitespace_in_banner_img(): void
    {
        $program = Program::at(
            "Programa Test",
            "Descripción válida del programa",
            "  banner_image.jpg  ",  // Con espacios
            "https://example.com",
            $this->validContact,
            $this->validProgramState
        );

        $this->assertEquals("banner_image.jpg", $program->banner_img);
    }

    public function test_throws_type_error_when_contact_is_not_instance_of_contact(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessageMatches('/Contact.*stdClass/');

        Program::at(
            "Programa Test",
            "Descripción válida del programa",
            "banner.jpg",
            "https://example.com",
            new \stdClass(),
            $this->validProgramState
        );
    }

    public function test_throws_type_error_when_program_state_is_not_instance_of_program_state(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessageMatches('/ProgramState.*stdClass/');

        Program::at(
            "Programa Test",
            "Descripción válida del programa",
            "banner.jpg",
            "https://example.com",
            $this->validContact,
            new \stdClass()
        );
    }



    public function test_can_create_program_with_unicode_characters_in_name(): void
    {
        $program = Program::at(
            "Programa Educación 2025",  // Con tilde
            "Descripción válida con ñ y tildes: ñáéíóú",
            "banner.jpg",
            "https://example.com",
            $this->validContact,
            $this->validProgramState
        );

        $this->assertEquals("Programa Educación 2025", $program->name);
        $this->assertEquals("Descripción válida con ñ y tildes: ñáéíóú", $program->description);
    }

}
