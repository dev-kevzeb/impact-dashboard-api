<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Program;
use App\Models\Country;
use App\Models\Agency;
use App\Models\ProgramState;
use App\Models\ProgramBeneficiary;
use App\Models\Donor;
use App\Models\Sdg;
use Exception;
use RuntimeException;

class ProgramTest extends TestCase
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



    public function test_program_can_be_created_with_valid_data()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg1 = Sdg::at("sdg1.png");
        $sdg2 = Sdg::at("sdg2.png");
        $donor1 = Donor::at("World Bank");
        $donor2 = Donor::at("UNICEF");
        
        $program = Program::at(
            name: "Programa de Desarrollo Rural",
            description: "Descripción del programa",
            bannerImg: "banner.jpg",
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            programUrl: "https://programa.com",
            contactFirstName: "Juan",
            contactLastName: "Pérez",
            contactTitle: "Director",
            contactEmail: "juan@email.com",
            contactPhone: "1234567890",
            programBeneficiary: $programBeneficiary,
            programState: $programState,
            country: $country,
            agency: $agency,
            sdgs: [$sdg1, $sdg2],
            programDonors: [$donor1, $donor2]
        );

        $this->assertEquals("Programa de Desarrollo Rural", $program->getName());
        $this->assertEquals("Descripción del programa", $program->getDescription());
        $this->assertEquals("banner.jpg", $program->getBannerImg());
        $this->assertEquals("2025-01-01", $program->getStartDate());
        $this->assertEquals("2025-12-31", $program->getEndDate());
        $this->assertEquals("https://programa.com", $program->getProgramUrl());
        $this->assertEquals("Juan", $program->getContactFirstName());
        $this->assertEquals("Pérez", $program->getContactLastName());
        $this->assertEquals("Director", $program->getContactTitle());
        $this->assertEquals("juan@email.com", $program->getContactEmail());
        $this->assertEquals("1234567890", $program->getContactPhone());
        $this->assertEquals($programBeneficiary, $program->getProgramBeneficiary());
        $this->assertEquals($programState, $program->getProgramState());
        $this->assertEquals($country, $program->getCountry());
        $this->assertEquals($agency, $program->getAgency());
        $this->assertEquals([$sdg1, $sdg2], $program->getSdgs());
        $this->assertEquals([$donor1, $donor2], $program->getProgramDonors());
        $this->assertInstanceOf(Program::class, $program);
    }

    // Tests de validación de nombre
    public function test_program_name_cannot_be_empty()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg, $donor) {
                Program::at(
                    name: "",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contactFirstName: "Juan",
                    contactLastName: "Pérez",
                    contactTitle: "Director",
                    contactEmail: "juan@email.com",
                    contactPhone: "1234567890",
                    programBeneficiary: $programBeneficiary,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el nombre del programa no debe ir vacio", $exception->getMessage());
            }
        );
    }

    public function test_program_name_too_short_throws_runtime_exception()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg, $donor) {
                Program::at(
                    name: "Ab",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contactFirstName: "Juan",
                    contactLastName: "Pérez",
                    contactTitle: "Director",
                    contactEmail: "juan@email.com",
                    contactPhone: "1234567890",
                    programBeneficiary: $programBeneficiary,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el nombre del programa debe tener al menos 3 caracteres", $exception->getMessage());
            }
        );
    }

    public function test_program_sdgs_can_be_empty_array()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $donor = Donor::at("World Bank");
        
        $program = Program::at(
            name: "Programa Test",
            description: "Descripción del programa",
            bannerImg: "banner.jpg",
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            programUrl: "https://programa.com",
            contactFirstName: "Juan",
            contactLastName: "Pérez",
            contactTitle: "Director",
            contactEmail: "juan@email.com",
            contactPhone: "1234567890",
            programBeneficiary: $programBeneficiary,
            programState: $programState,
            country: $country,
            agency: $agency,
            sdgs: [], 
            programDonors: [$donor]
        );

        $this->assertEquals([], $program->getSdgs());
        $this->assertInstanceOf(Program::class, $program);
    }

    public function test_program_donors_can_be_empty_array()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        
        $program = Program::at(
            name: "Programa Test",
            description: "Descripción del programa",
            bannerImg: "banner.jpg",
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            programUrl: "https://programa.com",
            contactFirstName: "Juan",
            contactLastName: "Pérez",
            contactTitle: "Director",
            contactEmail: "juan@email.com",
            contactPhone: "1234567890",
            programBeneficiary: $programBeneficiary,
            programState: $programState,
            country: $country,
            agency: $agency,
            sdgs: [$sdg],
            programDonors: [] 
        );

        $this->assertEquals([], $program->getProgramDonors());
        $this->assertInstanceOf(Program::class, $program);
    }

    // Tests para program URL
    public function test_program_url_can_be_empty_string()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $program = Program::at(
            name: "Programa Test",
            description: "Descripción del programa",
            bannerImg: "banner.jpg",
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            programUrl: "", 
            contactFirstName: "Juan",
            contactLastName: "Pérez",
            contactTitle: "Director",
            contactEmail: "juan@email.com",
            contactPhone: "1234567890",
            programBeneficiary: $programBeneficiary,
            programState: $programState,
            country: $country,
            agency: $agency,
            sdgs: [$sdg],
            programDonors: [$donor]
        );

        $this->assertEquals("", $program->getProgramUrl());
        $this->assertInstanceOf(Program::class, $program);
    }

    public function test_contact_phone_can_be_empty_string()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
     
        $program = Program::at(
            name: "Programa Test",
            description: "Descripción del programa",
            bannerImg: "banner.jpg",
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            programUrl: "https://programa.com",
            contactFirstName: "Juan",
            contactLastName: "Pérez",
            contactTitle: "Director",
            contactEmail: "juan@email.com",
            contactPhone: "", // String vacía permitida
            programBeneficiary: $programBeneficiary,
            programState: $programState,
            country: $country,
            agency: $agency,
            sdgs: [$sdg],
            programDonors: [$donor]
        );

        $this->assertEquals("", $program->getContactPhone());
        $this->assertInstanceOf(Program::class, $program);
    }

    // Tests de validación de description
    public function test_program_description_cannot_be_empty()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg, $donor) {
                Program::at(
                    name: "Programa Test",
                    description: "",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contactFirstName: "Juan",
                    contactLastName: "Pérez",
                    contactTitle: "Director",
                    contactEmail: "juan@email.com",
                    contactPhone: "1234567890",
                    programBeneficiary: $programBeneficiary,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la descripción del programa no debe ir vacio", $exception->getMessage());
            }
        );
    }

    public function test_program_description_too_short_throws_runtime_exception()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg, $donor) {
                Program::at(
                    name: "Programa Test",
                    description: "Corta",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contactFirstName: "Juan",
                    contactLastName: "Pérez",
                    contactTitle: "Director",
                    contactEmail: "juan@email.com",
                    contactPhone: "1234567890",
                    programBeneficiary: $programBeneficiary,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la descripción del programa debe tener al menos 10 caracteres", $exception->getMessage());
            }
        );
    }

    public function test_program_description_too_long_throws_runtime_exception()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $longDescription = str_repeat("a", 2001); // 2001 caracteres
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg, $donor, $longDescription) {
                Program::at(
                    name: "Programa Test",
                    description: $longDescription,
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contactFirstName: "Juan",
                    contactLastName: "Pérez",
                    contactTitle: "Director",
                    contactEmail: "juan@email.com",
                    contactPhone: "1234567890",
                    programBeneficiary: $programBeneficiary,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la descripción del programa no debe exceder 2000 caracteres", $exception->getMessage());
            }
        );
    }

    // Tests de validación de fechas
    public function test_program_start_date_cannot_be_empty()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg, $donor) {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contactFirstName: "Juan",
                    contactLastName: "Pérez",
                    contactTitle: "Director",
                    contactEmail: "juan@email.com",
                    contactPhone: "1234567890",
                    programBeneficiary: $programBeneficiary,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la fecha de inicio del programa no debe ir vacio", $exception->getMessage());
            }
        );
    }

    public function test_program_start_date_invalid_format_throws_runtime_exception()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg, $donor) {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "01/01/2025",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contactFirstName: "Juan",
                    contactLastName: "Pérez",
                    contactTitle: "Director",
                    contactEmail: "juan@email.com",
                    contactPhone: "1234567890",
                    programBeneficiary: $programBeneficiary,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la fecha de inicio debe tener formato válido (YYYY-MM-DD)", $exception->getMessage());
            }
        );
    }

    public function test_program_end_date_cannot_be_empty()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg, $donor) {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "",
                    programUrl: "https://programa.com",
                    contactFirstName: "Juan",
                    contactLastName: "Pérez",
                    contactTitle: "Director",
                    contactEmail: "juan@email.com",
                    contactPhone: "1234567890",
                    programBeneficiary: $programBeneficiary,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la fecha de fin del programa no debe ir vacio", $exception->getMessage());
            }
        );
    }

    public function test_program_end_date_before_start_date_throws_runtime_exception()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg, $donor) {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-12-31",
                    endDate: "2025-01-01",
                    programUrl: "https://programa.com",
                    contactFirstName: "Juan",
                    contactLastName: "Pérez",
                    contactTitle: "Director",
                    contactEmail: "juan@email.com",
                    contactPhone: "1234567890",
                    programBeneficiary: $programBeneficiary,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la fecha de fin debe ser posterior a la fecha de inicio", $exception->getMessage());
            }
        );
    }

    public function test_program_duration_too_long_throws_runtime_exception()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg, $donor) {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2050-01-01", // 25 años de duración
                    programUrl: "https://programa.com",
                    contactFirstName: "Juan",
                    contactLastName: "Pérez",
                    contactTitle: "Director",
                    contactEmail: "juan@email.com",
                    contactPhone: "1234567890",
                    programBeneficiary: $programBeneficiary,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la duración del programa no puede exceder 20 años", $exception->getMessage());
            }
        );
    }

    // Tests de validación de contacto
    public function test_contact_first_name_cannot_be_empty()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg100.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg, $donor) {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contactFirstName: "",
                    contactLastName: "Pérez",
                    contactTitle: "Director",
                    contactEmail: "juan@email.com",
                    contactPhone: "1234567890",
                    programBeneficiary: $programBeneficiary,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el nombre del contacto no debe ir vacio", $exception->getMessage());
            }
        );
    }

    public function test_contact_last_name_cannot_be_empty()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg, $donor) {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contactFirstName: "Juan",
                    contactLastName: "",
                    contactTitle: "Director",
                    contactEmail: "juan@email.com",
                    contactPhone: "1234567890",
                    programBeneficiary: $programBeneficiary,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el apellido del contacto no debe ir vacio", $exception->getMessage());
            }
        );
    }

    public function test_contact_title_cannot_be_empty()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg, $donor) {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contactFirstName: "Juan",
                    contactLastName: "Pérez",
                    contactTitle: "",
                    contactEmail: "juan@email.com",
                    contactPhone: "1234567890",
                    programBeneficiary: $programBeneficiary,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el título del contacto no debe ir vacio", $exception->getMessage());
            }
        );
    }

    public function test_contact_email_cannot_be_empty()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg, $donor) {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contactFirstName: "Juan",
                    contactLastName: "Pérez",
                    contactTitle: "Director",
                    contactEmail: "",
                    contactPhone: "1234567890",
                    programBeneficiary: $programBeneficiary,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el email del contacto no debe ir vacio", $exception->getMessage());
            }
        );
    }

    public function test_contact_email_invalid_format_throws_runtime_exception()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg, $donor) {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contactFirstName: "Juan",
                    contactLastName: "Pérez",
                    contactTitle: "Director",
                    contactEmail: "email-invalido",
                    contactPhone: "1234567890",
                    programBeneficiary: $programBeneficiary,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el email del contacto debe tener un formato válido", $exception->getMessage());
            }
        );
    }

    // Test para verificar que SDGs duplicados lanzan excepción
    public function test_program_with_duplicate_sdgs_throws_runtime_exception()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg1 = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg1, $donor) {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contactFirstName: "Juan",
                    contactLastName: "Pérez",
                    contactTitle: "Director",
                    contactEmail: "juan@email.com",
                    contactPhone: "1234567890",
                    programBeneficiary: $programBeneficiary,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg1, $sdg1], // SDG duplicado
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("no se permiten SDGs duplicados en el programa", $exception->getMessage());
            }
        );
    }

    // Test para verificar que Donors duplicados lanzan excepción
    public function test_program_with_duplicate_donors_throws_runtime_exception()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor1 = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg, $donor1) {
                Program::at(
                    name: "Programa Test",
                    description: "Descripción del programa",
                    bannerImg: "banner.jpg",
                    startDate: "2025-01-01",
                    endDate: "2025-12-31",
                    programUrl: "https://programa.com",
                    contactFirstName: "Juan",
                    contactLastName: "Pérez",
                    contactTitle: "Director",
                    contactEmail: "juan@email.com",
                    contactPhone: "1234567890",
                    programBeneficiary: $programBeneficiary,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor1, $donor1] // Donor duplicado
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("no se permiten donantes duplicados en el programa", $exception->getMessage());
            }
        );
    }
}