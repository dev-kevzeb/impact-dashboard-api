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

    // Helper method para crear programas con valores por defecto usando objetos
    private function createProgramWith($overrides = [])
    {
        // Crear objetos por defecto
        $defaultCountry = Country::at("Bolivia");
        $defaultAgency = Agency::at("UNICEF");
        $defaultProgramBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $defaultProgramState = ProgramState::at("ACTIVE");
        $defaultSdg = Sdg::at("sdg1.png");
        $defaultDonor = Donor::at("World Bank");
        
        $defaults = [
            "Programa Test",                    // 0 - name
            "Descripción test",                 // 1 - description
            "banner.jpg",                       // 2 - banner_img
            "2025-01-01",                      // 3 - start_date
            "2025-12-31",                      // 4 - end_date
            "https://test.com",                // 5 - program_url
            "Juan",                            // 6 - contact_first_name
            "Pérez",                           // 7 - contact_last_name
            "Director",                        // 8 - contact_title
            "juan@test.com",                   // 9 - contact_email
            1234567890,                        // 10 - contact_phone
            $defaultProgramBeneficiary,        // 11 - program_beneficiary object
            $defaultProgramState,              // 12 - program_state object
            $defaultCountry,                   // 13 - country object
            $defaultAgency,                    // 14 - agency object
            [$defaultSdg],                     // 15 - sdgs array of objects
            [$defaultDonor]                    // 16 - donors array of objects
        ];

        // Aplicar overrides
        foreach ($overrides as $index => $value) {
            $defaults[$index] = $value;
        }

        return Program::at(...$defaults);
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
            "Programa de Desarrollo Rural",
            "Descripción del programa",
            "banner.jpg",
            "2025-01-01",
            "2025-12-31",
            "https://programa.com",
            "Juan",
            "Pérez",
            "Director",
            "juan@email.com",
            1234567890,
            $programBeneficiary,
            $programState,
            $country,
            $agency,
            [$sdg1, $sdg2],
            [$donor1, $donor2]
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
        $this->assertEquals(1234567890, $program->getContactPhone());
        $this->assertEquals($programBeneficiary, $program->getProgramBeneficiary());
        $this->assertEquals($programState, $program->getProgramState());
        $this->assertEquals($country, $program->getCountry());
        $this->assertEquals($agency, $program->getAgency());
        $this->assertEquals([$sdg1, $sdg2], $program->getSdgs());
        $this->assertEquals([$donor1, $donor2], $program->getProgramDonors());
        $this->assertInstanceOf(Program::class, $program);
    }

    // Tests de validación de nombre
    public function test_program_name_cannot_be_null()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createProgramWith([0 => null]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el nombre del programa no debe ser null", $exception->getMessage());
            }
        );
    }

    public function test_program_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createProgramWith([0 => ""]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el nombre del programa no debe ir vacio", $exception->getMessage());
            }
        );
    }

    // Tests de validación de Country
    public function test_program_country_cannot_be_null()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createProgramWith([13 => null]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el país del programa no debe ser null", $exception->getMessage());
            }
        );
    }

    public function test_program_country_must_be_country_object()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createProgramWith([13 => "Bolivia"]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el país debe ser una instancia de Country", $exception->getMessage());
            }
        );
    }

    public function test_program_country_validates_correctly()
    {
        $country = Country::at("Bolivia");
        $program = $this->createProgramWith([13 => $country]);
        
        $this->assertEquals($country, $program->getCountry());
        $this->assertTrue($country->validateName());
    }

    // Tests de validación de Agency
    public function test_program_agency_cannot_be_null()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createProgramWith([14 => null]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la agencia del programa no debe ser null", $exception->getMessage());
            }
        );
    }

    public function test_program_agency_must_be_agency_object()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createProgramWith([14 => "UNICEF"]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la agencia debe ser una instancia de Agency", $exception->getMessage());
            }
        );
    }

    public function test_program_agency_validates_correctly()
    {
        $agency = Agency::at("UNICEF");
        $program = $this->createProgramWith([14 => $agency]);
        
        $this->assertEquals($agency, $program->getAgency());
        $this->assertEquals("UNICEF", $agency->getName());
    }

    // Tests de validación de ProgramBeneficiary
    public function test_program_beneficiary_cannot_be_null()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createProgramWith([11 => null]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el beneficiario del programa no debe ser null", $exception->getMessage());
            }
        );
    }

    public function test_program_beneficiary_must_be_program_beneficiary_object()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createProgramWith([11 => "GOVERNMENT"]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el beneficiario debe ser una instancia de ProgramBeneficiary", $exception->getMessage());
            }
        );
    }

    public function test_program_beneficiary_validates_correctly()
    {
        $programBeneficiary = ProgramBeneficiary::at("GOVERNMENT");
        $program = $this->createProgramWith([11 => $programBeneficiary]);
        
        $this->assertEquals($programBeneficiary, $program->getProgramBeneficiary());
        $this->assertTrue($programBeneficiary->validateName());
        $this->assertTrue($programBeneficiary->isGovernment());
    }

    // Tests de validación de ProgramState
    public function test_program_state_cannot_be_null()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createProgramWith([12 => null]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el estado del programa no debe ser null", $exception->getMessage());
            }
        );
    }

    public function test_program_state_must_be_program_state_object()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createProgramWith([12 => "ACTIVE"]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el estado debe ser una instancia de ProgramState", $exception->getMessage());
            }
        );
    }

    public function test_program_state_validates_correctly()
    {
        $programState = ProgramState::at("ACTIVE");
        $program = $this->createProgramWith([12 => $programState]);
        
        $this->assertEquals($programState, $program->getProgramState());
        $this->assertTrue($programState->validateState());
        $this->assertTrue($programState->isActive());
    }

    // Tests de validación de SDGs
    public function test_program_sdgs_cannot_be_null()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createProgramWith([15 => null]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("los SDGs del programa no deben ser null", $exception->getMessage());
            }
        );
    }

    public function test_program_sdgs_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createProgramWith([15 => []]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("debe seleccionar al menos un SDG", $exception->getMessage());
            }
        );
    }

    public function test_program_sdgs_must_be_sdg_objects()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createProgramWith([15 => ["sdg1.png", "sdg2.png"]]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("todos los SDGs deben ser instancias de Sdg", $exception->getMessage());
            }
        );
    }

    public function test_program_sdgs_validates_correctly()
    {
        $sdg1 = Sdg::at("sdg1.png");
        $sdg2 = Sdg::at("sdg2.png");
        $program = $this->createProgramWith([15 => [$sdg1, $sdg2]]);
        
        $this->assertEquals([$sdg1, $sdg2], $program->getSdgs());
        $this->assertTrue($sdg1->validateImage());
        $this->assertTrue($sdg2->validateImage());
    }

    // Tests de validación de Donors
    public function test_program_donors_cannot_be_null()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createProgramWith([16 => null]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("los donantes del programa no deben ser null", $exception->getMessage());
            }
        );
    }

    public function test_program_donors_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createProgramWith([16 => []]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("debe seleccionar al menos un donante", $exception->getMessage());
            }
        );
    }

    public function test_program_donors_must_be_donor_objects()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createProgramWith([16 => ["World Bank", "UNICEF"]]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("todos los donantes deben ser instancias de Donor", $exception->getMessage());
            }
        );
    }

    public function test_program_donors_validates_correctly()
    {
        $donor1 = Donor::at("World Bank");
        $donor2 = Donor::at("UNICEF");
        $program = $this->createProgramWith([16 => [$donor1, $donor2]]);
        
        $this->assertEquals([$donor1, $donor2], $program->getProgramDonors());
        $this->assertTrue($donor1->validateName());
        $this->assertTrue($donor2->validateName());
    }
}