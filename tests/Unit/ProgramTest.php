<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Program;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Agency;
use App\Models\ProgramState;
use App\Models\ProgramBeneficiary;
use App\Models\Donor;
use App\Models\Sdg;
use App\Models\Contact;
use Exception;
use RuntimeException;

class ProgramTest extends TestCase
{
    private Country $validCountry;
    private Agency $validAgency;
    private ProgramBeneficiary $validProgramBeneficiary;
    private ProgramState $validProgramState;
    private Sdg $validSdg1;
    private Sdg $validSdg2;
    private Donor $validDonor1;
    private Donor $validDonor2;
    private Contact $validContact;
    private array $validSdgs;
    private array $validDonors;

    protected function setUp(): void
    {
        parent::setUp();
        
        //objetos válidos reutilizables
        $validCurrency = Currency::at("USD");
        $this->validCountry = Country::at("Bolivia", $validCurrency);
        $this->validAgency = Agency::at("UNICEF", "https://www.unicef.org", true);
        $this->validProgramBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $this->validProgramState = ProgramState::at("ACTIVE");
        $this->validSdg1 = Sdg::at("sdg1.png");
        $this->validSdg2 = Sdg::at("sdg2.png");
        $this->validDonor1 = Donor::at("World Bank");
        $this->validDonor2 = Donor::at("USAID");
        $this->validContact = Contact::at("Juan", "Pérez", "Director", "juan@email.com", "1234567890");
        
        // Arrays para usar en tests
        $this->validSdgs = [$this->validSdg1, $this->validSdg2];
        $this->validDonors = [$this->validDonor1, $this->validDonor2];
    }

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



    public function test_program_can_be_created_with_valid_data()
    {
        $program = Program::at(
            name: "Programa de Desarrollo Rural",
            description: "Descripción del programa",
            bannerImg: "banner.jpg",
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            programUrl: "https://programa.com",
            contact: $this->validContact,
            programBeneficiary: $this->validProgramBeneficiary,
            programState: $this->validProgramState,
            country: $this->validCountry,
            agency: $this->validAgency,
            sdgs: $this->validSdgs,
            programDonors: $this->validDonors
        );

        $this->assertEquals("Programa de Desarrollo Rural", $program->getName());
        $this->assertEquals("Descripción del programa", $program->getDescription());
        $this->assertEquals("banner.jpg", $program->getBannerImg());
        $this->assertEquals("2025-01-01", $program->getStartDate());
        $this->assertEquals("2025-12-31", $program->getEndDate());
        $this->assertEquals("https://programa.com", $program->getProgramUrl());
        $this->assertEquals($this->validContact, $program->getContact());
        $this->assertEquals($this->validProgramBeneficiary, $program->getProgramBeneficiary());
        $this->assertEquals($this->validProgramState, $program->getProgramState());
        $this->assertEquals($this->validCountry, $program->getCountry());
        $this->assertEquals($this->validAgency, $program->getAgency());
        $this->assertEquals($this->validSdgs, $program->getSdgs());
        $this->assertEquals($this->validDonors, $program->getProgramDonors());
        $this->assertInstanceOf(Program::class, $program);
    }

    // Tests de validación de nombre
    public function test_program_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Program::at(
                    name: "",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contact: $this->validContact,
                    programBeneficiary: $this->validProgramBeneficiary,
                    programState: $this->validProgramState,
                    country: $this->validCountry,
                    agency: $this->validAgency,
                    sdgs: [$this->validSdg1],
                    programDonors: [$this->validDonor1]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Program::$ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_program_name_cannot_be_empty2()
    {
        $this->shouldThrowAndAssert(
            function () {
                Program::at(
                    name: "",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contact: $this->validContact,
                    programBeneficiary: $this->validProgramBeneficiary,
                    programState: $this->validProgramState,
                    country: $this->validCountry,
                    agency: $this->validAgency,
                    sdgs: [$this->validSdg1, $this->validSdg2],
                    programDonors: [$this->validDonor1]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Program::$ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_program_name_too_short_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                Program::at(
                    name: "Ab", // Solo 2 caracteres - debería fallar
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contact: $this->validContact,
                    programBeneficiary: $this->validProgramBeneficiary,
                    programState: $this->validProgramState,
                    country: $this->validCountry,
                    agency: $this->validAgency,
                    sdgs: [$this->validSdg1],
                    programDonors: [$this->validDonor1]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Program::$ERROR_NAME_MIN_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_program_sdgs_can_be_empty_array()
    {
        $program = Program::at(
            name: "Programa Test",
            description: "Descripción del programa",
            bannerImg: "banner.jpg",
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            programUrl: "https://programa.com",
            contact: $this->validContact,
            programBeneficiary: $this->validProgramBeneficiary,
            programState: $this->validProgramState,
            country: $this->validCountry,
            agency: $this->validAgency,
            sdgs: [], 
            programDonors: [$this->validDonor1]
        );

        $this->assertEquals([], $program->getSdgs());
        $this->assertInstanceOf(Program::class, $program);
    }

    public function test_program_donors_can_be_empty_array()
    {
        $program = Program::at(
            name: "Programa Test",
            description: "Descripción del programa",
            bannerImg: "banner.jpg",
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            programUrl: "https://programa.com",
            contact: $this->validContact,
            programBeneficiary: $this->validProgramBeneficiary,
            programState: $this->validProgramState,
            country: $this->validCountry,
            agency: $this->validAgency,
            sdgs: [$this->validSdg1],
            programDonors: [] 
        );

        $this->assertEquals([], $program->getProgramDonors());
        $this->assertInstanceOf(Program::class, $program);
    }

    // Tests para program URL
    public function test_program_url_can_be_empty_string()
    {
        $program = Program::at(
            name: "Programa Test",
            description: "Descripción del programa",
            bannerImg: "banner.jpg",
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            programUrl: "", 
            contact: $this->validContact,
            programBeneficiary: $this->validProgramBeneficiary,
            programState: $this->validProgramState,
            country: $this->validCountry,
            agency: $this->validAgency,
            sdgs: [$this->validSdg1],
            programDonors: [$this->validDonor1]
        );

        $this->assertEquals("", $program->getProgramUrl());
        $this->assertInstanceOf(Program::class, $program);
    }

    // Tests de validación de description
    public function test_program_description_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Program::at(
                    name: "Programa Test",
                    description: "",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contact: $this->validContact,
                    programBeneficiary: $this->validProgramBeneficiary,
                    programState: $this->validProgramState,
                    country: $this->validCountry,
                    agency: $this->validAgency,
                    sdgs: [$this->validSdg1],
                    programDonors: [$this->validDonor1]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Program::$ERROR_DESCRIPTION_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_program_description_too_short_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                Program::at(
                    name: "Programa Test",
                    description: "Corta",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contact: $this->validContact,
                    programBeneficiary: $this->validProgramBeneficiary,
                    programState: $this->validProgramState,
                    country: $this->validCountry,
                    agency: $this->validAgency,
                    sdgs: [$this->validSdg1],
                    programDonors: [$this->validDonor1]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Program::$ERROR_DESCRIPTION_MIN_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_program_description_too_long_throws_runtime_exception()
    {
        
        $longDescription = str_repeat("a", 2001); // 2001 caracteres
        
        $this->shouldThrowAndAssert(
            function () use ($longDescription) {
                Program::at(
                    name: "Programa Test",
                    description: $longDescription,
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contact: $this->validContact,
                    programBeneficiary: $this->validProgramBeneficiary,
                    programState: $this->validProgramState,
                    country: $this->validCountry,
                    agency: $this->validAgency,
                    sdgs: [$this->validSdg1],
                    programDonors: [$this->validDonor1]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Program::$ERROR_DESCRIPTION_MAX_LENGTH, $exception->getMessage());
            }
        );
    }

    // Tests de validación de fechas
    public function test_program_start_date_cannot_be_empty()
    {
        
        $this->shouldThrowAndAssert(
            function () {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contact: $this->validContact,
                    programBeneficiary: $this->validProgramBeneficiary,
                    programState: $this->validProgramState,
                    country: $this->validCountry,
                    agency: $this->validAgency,
                    sdgs: [$this->validSdg1],
                    programDonors: [$this->validDonor1]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Program::$ERROR_START_DATE_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_program_start_date_invalid_format_throws_runtime_exception()
    {
        
        $this->shouldThrowAndAssert(
            function () {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "01/01/2025",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contact: $this->validContact,
                    programBeneficiary: $this->validProgramBeneficiary,
                    programState: $this->validProgramState,
                    country: $this->validCountry,
                    agency: $this->validAgency,
                    sdgs: [$this->validSdg1],
                    programDonors: [$this->validDonor1]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Program::$ERROR_START_DATE_INVALID_FORMAT, $exception->getMessage());
            }
        );
    }

    public function test_program_end_date_cannot_be_empty()
    {
        
        $this->shouldThrowAndAssert(
            function () {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "",
                    programUrl: "https://programa.com",
                    contact: $this->validContact,
                    programBeneficiary: $this->validProgramBeneficiary,
                    programState: $this->validProgramState,
                    country: $this->validCountry,
                    agency: $this->validAgency,
                    sdgs: [$this->validSdg1],
                    programDonors: [$this->validDonor1]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Program::$ERROR_END_DATE_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_program_end_date_before_start_date_throws_runtime_exception()
    {
        
        $this->shouldThrowAndAssert(
            function () {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-12-31",
                    endDate: "2025-01-01",
                    programUrl: "https://programa.com",
                    contact: $this->validContact,
                    programBeneficiary: $this->validProgramBeneficiary,
                    programState: $this->validProgramState,
                    country: $this->validCountry,
                    agency: $this->validAgency,
                    sdgs: [$this->validSdg1],
                    programDonors: [$this->validDonor1]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Program::$ERROR_END_DATE_BEFORE_START, $exception->getMessage());
            }
        );
    }

    public function test_program_duration_too_long_throws_runtime_exception()
    {
        
        $this->shouldThrowAndAssert(
            function () {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2050-01-01", // 25 años de duración
                    programUrl: "https://programa.com",
                    contact: $this->validContact,
                    programBeneficiary: $this->validProgramBeneficiary,
                    programState: $this->validProgramState,
                    country: $this->validCountry,
                    agency: $this->validAgency,
                    sdgs: [$this->validSdg1],
                    programDonors: [$this->validDonor1]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Program::$ERROR_DURATION_TOO_LONG, $exception->getMessage());
            }
        );
    }

    // Test para verificar que SDGs duplicados lanzan excepción
    public function test_program_with_duplicate_sdgs_throws_runtime_exception()
    {
        $sdg1 = Sdg::at("sdg1.png");
        
        $this->shouldThrowAndAssert(
            function () {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contact: $this->validContact,
                    programBeneficiary: $this->validProgramBeneficiary,
                    programState: $this->validProgramState,
                    country: $this->validCountry,
                    agency: $this->validAgency,
                    sdgs: [$this->validSdg1, $this->validSdg1], // SDG duplicado
                    programDonors: [$this->validDonor1]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Program::$ERROR_SDGS_DUPLICATED, $exception->getMessage());
            }
        );
    }

    // Test para verificar que Donors duplicados lanzan excepción
    public function test_program_with_duplicate_donors_throws_runtime_exception()
    {
        $donor1 = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contact: $this->validContact,
                    programBeneficiary: $this->validProgramBeneficiary,
                    programState: $this->validProgramState,
                    country: $this->validCountry,
                    agency: $this->validAgency,
                    sdgs: [$this->validSdg1],
                    programDonors: [$this->validDonor1, $this->validDonor1] // Donor duplicado
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Program::$ERROR_DONORS_DUPLICATED, $exception->getMessage());
            }
        );
    }
}