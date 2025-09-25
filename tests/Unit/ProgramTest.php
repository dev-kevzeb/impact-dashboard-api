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
    // Closure para manejo de errores - patrón del proyecto
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

    // Tests de validación de Country
    public function test_program_country_cannot_be_null()
    {
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($agency, $programBeneficiary, $programState, $sdg, $donor) {
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
                    country: null,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el país del programa no debe ser null", $exception->getMessage());
            }
        );
    }

    // Tests de validación de Agency
    public function test_program_agency_cannot_be_null()
    {
        $country = Country::at("Bolivia");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $programBeneficiary, $programState, $sdg, $donor) {
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
                    agency: null,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la agencia del programa no debe ser null", $exception->getMessage());
            }
        );
    }

    // Tests de validación de ProgramBeneficiary
    public function test_program_beneficiary_cannot_be_null()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programState, $sdg, $donor) {
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
                    programBeneficiary: null,
                    programState: $programState,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el beneficiario del programa no debe ser null", $exception->getMessage());
            }
        );
    }

    // Tests de validación de ProgramState
    public function test_program_state_cannot_be_null()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $sdg = Sdg::at("sdg1.png");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $sdg, $donor) {
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
                    programState: null,
                    country: $country,
                    agency: $agency,
                    sdgs: [$sdg],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el estado del programa no debe ser null", $exception->getMessage());
            }
        );
    }

    // Tests de validación de SDGs
    public function test_program_sdgs_cannot_be_null()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $donor) {
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
                    sdgs: null,
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("los SDGs del programa no deben ser null", $exception->getMessage());
            }
        );
    }

    public function test_program_sdgs_cannot_be_empty()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $donor = Donor::at("World Bank");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $donor) {
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
                    sdgs: [],
                    programDonors: [$donor]
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("debe seleccionar al menos un SDG", $exception->getMessage());
            }
        );
    }

    // Tests de validación de Donors
    public function test_program_donors_cannot_be_null()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg) {
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
                    programDonors: null
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("los donantes del programa no deben ser null", $exception->getMessage());
            }
        );
    }

    public function test_program_donors_cannot_be_empty()
    {
        $country = Country::at("Bolivia");
        $agency = Agency::at("UNICEF");
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $programState = ProgramState::at("ACTIVE");
        $sdg = Sdg::at("sdg1.png");
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $programBeneficiary, $programState, $sdg) {
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
                    programDonors: []
                );
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("debe seleccionar al menos un donante", $exception->getMessage());
            }
        );
    }
}