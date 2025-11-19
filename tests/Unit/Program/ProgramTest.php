<?php

namespace Tests\Unit\Program;

use PHPUnit\Framework\TestCase;
use App\Modules\Program\Domain\Program;
use App\Modules\Country\Domain\Country;
use App\Modules\Currency\Domain\Currency;
use App\Modules\Agency\Domain\Agency;
use App\Modules\ProgramState\Domain\ProgramState;
use App\Modules\Beneficiary\Domain\Beneficiary;
use App\Modules\Donor\Domain\Donor;
use App\Modules\Sdg\Domain\Sdg;
use App\Modules\Contact\Domain\Contact;
use RuntimeException;


class ProgramTest extends TestCase
{
    private Country $validCountry;
    private Agency $validAgency;
    private Beneficiary $validBeneficiary;
    private ProgramState $validProgramState;
    private Contact $validContact;
    private Currency $validCurrency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validCurrency = Currency::at("USD");
        $this->validCountry = Country::at("Costa Rica", $this->validCurrency);
        $this->validAgency = Agency::at("UNICEF", "https://www.unicef.org", true);
        $this->validBeneficiary = Beneficiary::at("Comunidades Rurales");
        $this->validProgramState = ProgramState::at("Activo");
        $this->validContact = Contact::at("Juan", "Pérez", "Director", "juan@example.com", "+50688888888");
    }

    public function test_can_create_program_with_valid_data(): void
    {
        $program = Program::at(
            "Programa de Educación Rural 2025",
            "Programa enfocado en mejorar la educación en zonas rurales mediante capacitación docente",
            "program_banners/banner.jpg",
            "2025-01-15",
            "2027-12-31",
            "https://www.programa-educacion.org",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
        );

        $this->assertInstanceOf(Program::class, $program);
        $this->assertEquals("Programa de Educación Rural 2025", $program->name);
        $this->assertEquals("Programa enfocado en mejorar la educación en zonas rurales mediante capacitación docente", $program->description);
        $this->assertEquals("program_banners/banner.jpg", $program->banner_img);
        $this->assertEquals("2025-01-15", $program->start_date);
        $this->assertEquals("2027-12-31", $program->end_date);
        $this->assertEquals("https://www.programa-educacion.org", $program->program_url);
    }

    public function test_can_create_program_with_minimum_valid_name(): void
    {
        $program = Program::at(
            "ABC",  // 3 caracteres (mínimo)
            "Descripción válida con más de 10 caracteres",
            "banner.jpg",
            "2025-01-01",
            "2025-12-31",
            "https://example.com",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
        );

        $this->assertEquals("ABC", $program->name);
    }

    public function test_can_create_program_with_minimum_valid_description(): void
    {
        $program = Program::at(
            "Programa Test",
            "1234567890",  // 10 caracteres (mínimo)
            "banner.jpg",
            "2025-01-01",
            "2025-12-31",
            "",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
        );

        $this->assertEquals("1234567890", $program->description);
    }

    public function test_can_create_program_with_maximum_duration_20_years(): void
    {
        $program = Program::at(
            "Programa Largo Plazo",
            "Programa de desarrollo con duración máxima permitida",
            "banner.jpg",
            "2025-01-01",
            "2044-12-31",  // Justo 20 años menos 1 día
            "",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
        );

        $this->assertInstanceOf(Program::class, $program);
    }

    public function test_trims_whitespace_from_inputs(): void
    {
        $program = Program::at(
            "  Programa con espacios  ",
            "  Descripción con espacios al inicio y final  ",
            "  banner.jpg  ",
            "  2025-01-01  ",
            "  2025-12-31  ",
            "  https://example.com  ",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
        );

        $this->assertEquals("Programa con espacios", $program->name);
        $this->assertEquals("Descripción con espacios al inicio y final", $program->description);
        $this->assertEquals("banner.jpg", $program->banner_img);
        $this->assertEquals("2025-01-01", $program->start_date);
        $this->assertEquals("2025-12-31", $program->end_date);
        $this->assertEquals("https://example.com", $program->program_url);
    }

    public function test_can_create_program_with_empty_url(): void
    {
        $program = Program::at(
            "Programa Test",
            "Descripción válida del programa",
            "banner.jpg",
            "2025-01-01",
            "2025-12-31",
            "",  // URL vacía es permitida
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
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
            "2025-01-01",
            "2025-12-31",
            "https://example.com",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
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
            "2025-01-01",
            "2025-12-31",
            "https://example.com",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
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
            "2025-01-01",
            "2025-12-31",
            "https://example.com",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
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
            "2025-01-01",
            "2025-12-31",
            "https://example.com",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
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
            "2025-01-01",
            "2025-12-31",
            "https://example.com",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
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
            "2025-01-01",
            "2025-12-31",
            "https://example.com",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
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
            "2025-01-01",
            "2025-12-31",
            "https://example.com",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
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
            "2025-01-01",
            "2025-12-31",
            "https://example.com",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
        );
    }

    public function test_throws_exception_when_end_date_is_before_start_date(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(Program::$ERROR_END_DATE_BEFORE_START);

        Program::at(
            "Programa Inválido",
            "Descripción válida del programa",
            "banner.jpg",
            "2025-12-31",
            "2025-01-01",  // Fecha fin antes de inicio
            "https://example.com",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
        );
    }

    public function test_can_create_program_when_end_date_equals_start_date(): void
    {
        $program = Program::at(
            "Programa Un Día",
            "Descripción válida del programa",
            "banner.jpg",
            "2025-01-01",
            "2025-01-01",  // Misma fecha - válido según la lógica actual
            "https://example.com",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
        );

        $this->assertInstanceOf(Program::class, $program);
    }

    public function test_throws_exception_when_duration_exceeds_20_years(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(Program::$ERROR_DURATION_TOO_LONG);

        Program::at(
            "Programa Muy Largo",
            "Descripción válida del programa con duración excesiva",
            "banner.jpg",
            "2025-01-01",
            "2046-01-01",  // 21 años (excede el máximo)
            "https://example.com",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
        );
    }

    public function test_throws_type_error_when_contact_is_not_instance_of_contact(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessageMatches('/Contact.*stdClass/');

        Program::at(
            "Programa Test",
            "Descripción válida del programa",
            "banner.jpg",
            "2025-01-01",
            "2025-12-31",
            "https://example.com",
            new \stdClass(),  
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
        );
    }

    public function test_throws_type_error_when_beneficiary_is_not_instance_of_beneficiary(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessageMatches('/Beneficiary.*stdClass/');

        Program::at(
            "Programa Test",
            "Descripción válida del programa",
            "banner.jpg",
            "2025-01-01",
            "2025-12-31",
            "https://example.com",
            $this->validContact,
            new \stdClass(),  
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
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
            "2025-01-01",
            "2025-12-31",
            "https://example.com",
            $this->validContact,
            $this->validBeneficiary,
            new \stdClass(), 
            $this->validCountry,
            $this->validAgency
        );
    }

    public function test_throws_type_error_when_country_is_not_instance_of_country(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessageMatches('/Country.*stdClass/');

        Program::at(
            "Programa Test",
            "Descripción válida del programa",
            "banner.jpg",
            "2025-01-01",
            "2025-12-31",
            "https://example.com",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            new \stdClass(), 
            $this->validAgency
        );
    }

    public function test_throws_type_error_when_agency_is_not_instance_of_agency(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessageMatches('/Agency.*stdClass/');

        Program::at(
            "Programa Test",
            "Descripción válida del programa",
            "banner.jpg",
            "2025-01-01",
            "2025-12-31",
            "https://example.com",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            new \stdClass() 
        );
    }

    public function test_can_create_program_with_exact_20_years_duration(): void
    {
        $program = Program::at(
            "Programa 20 Años Exactos",
            "Programa con duración exacta de 20 años",
            "banner.jpg",
            "2025-01-01",
            "2045-01-01",  // Exactamente 20 años
            "https://example.com",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
        );

        $this->assertInstanceOf(Program::class, $program);
    }

    public function test_can_create_program_with_unicode_characters_in_name(): void
    {
        $program = Program::at(
            "Programa Educación 2025",  // Con tilde
            "Descripción válida con ñ y tildes: ñáéíóú",
            "banner.jpg",
            "2025-01-01",
            "2025-12-31",
            "https://example.com",
            $this->validContact,
            $this->validBeneficiary,
            $this->validProgramState,
            $this->validCountry,
            $this->validAgency
        );

        $this->assertEquals("Programa Educación 2025", $program->name);
        $this->assertEquals("Descripción válida con ñ y tildes: ñáéíóú", $program->description);
    }

}
