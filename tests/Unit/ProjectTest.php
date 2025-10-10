<?php

namespace Tests\Unit;

use App\Models\Agency;
use App\Models\Beneficiary;
use App\Models\Contact;
use PHPUnit\Framework\TestCase;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Donor;
use App\Models\Indicator;
use App\Models\IndicatorType;
use App\Models\Kpa;
use App\Models\Measure;
use App\Models\Project;
use App\Models\ProjectDonor;
use App\Models\ProjectState;
use App\Models\StrategicOutput;
use Exception;
use RuntimeException;

class ProjectTest extends TestCase
{
    private Country $validCountry;
    private Agency $validAgency;
    private Indicator $validIndicator;
    private ProjectState $validProjectState;
    private Contact $validContact;
    private Beneficiary $validProjectBeneficiary;
    private array $validProjectDonors;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->validCountry = Country::at("Pais Valido", Currency::at("ARS"));
        $this->validAgency = Agency::at("Agencia Valida", "https://www.agencia.com", true);
        $this->validProjectState = ProjectState::at("Estado Valido");
        $this->validContact = Contact::at("Nombre Valido", "Apellido valido", "titulo valido", "contacto@ejemplo.com", "123456789");  
        $this->validProjectBeneficiary = Beneficiary::at("GOVERNMENT");
        
        $validIndicatorType = IndicatorType::at("Tipo Valido");
        $this->validIndicator = Indicator::at("Indicador Valido", $validIndicatorType, 100);
        
        $donor1 = Donor::at("USAID");
        $donor2 = Donor::at("World Bank");
        $this->validProjectDonors = [
            ProjectDonor::at($donor1, 60),
            ProjectDonor::at($donor2, 40)
        ];
    }

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
    // Método para crear Project válido 
    private function createValidProject(array $overrides = []): Project
    {
        $defaults = [
            'name' => 'Proyecto de Desarrollo Rural',
            'description' => 'Descripción válida del proyecto de desarrollo rural con más de 10 caracteres',
            'projectUrl' => 'https://proyecto.example.com',
            'startDate' => '2025-01-01',
            'endDate' => '2026-12-31',
            'progress' => 75.5,
            'comments' => 'Comentarios del proyecto en progreso',
            'projectBudget' => 500000.0,
            'shared' => true,
            'contact' => $this->validContact,
            'projectBeneficiary' => $this->validProjectBeneficiary,
            'projectState' => $this->validProjectState,
            'country' => $this->validCountry,
            'agency' => $this->validAgency,
            'indicator' => $this->validIndicator,
            'projectDonors' => $this->validProjectDonors
        ];

        $params = array_merge($defaults, $overrides);
        return Project::at(...array_values($params));
    }
    
    public function test_project_can_be_created_with_valid_data()
    {
        $project = $this->createValidProject();

        $this->assertEquals("Proyecto de Desarrollo Rural", $project->getName());
        $this->assertInstanceOf(Project::class, $project);
        $this->assertEquals("https://proyecto.example.com", $project->getProjectUrl());
        $this->assertEquals(75.5, $project->getProgress());
        $this->assertInstanceOf(Beneficiary::class, $project->getProjectBeneficiary());
        $this->assertIsArray($project->getProjectDonors());
        $this->assertCount(2, $project->getProjectDonors());
        $this->assertEquals("GOVERNMENT", $project->getProjectBeneficiary()->getName());
    }

    // Tests de validación de nombre
    public function test_name_empty_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createValidProject(['name' => '']);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_name_only_spaces_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createValidProject(['name' => '   ']);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$ERROR_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_name_too_short_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createValidProject(['name' => 'ab']);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$ERROR_NAME_MIN_LENGTH, $exception->getMessage());
            }
        );
    }

    // Tests de validación de descripción
    public function test_description_empty_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createValidProject(['description' => '']);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$ERROR_DESCRIPTION_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_description_too_short_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createValidProject(['description' => 'abc']);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$ERROR_DESCRIPTION_MIN_LENGTH, $exception->getMessage());
            }
        );
    }

    // Tests de validación de URL
    public function test_project_url_invalid_format_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createValidProject(['projectUrl' => 'invalid-url']);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$ERROR_PROJECT_URL_INVALID_FORMAT, $exception->getMessage());
            }
        );
    }

    // Tests de validación de fechas
    public function test_start_date_empty_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createValidProject(['startDate' => '']);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$ERROR_START_DATE_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_end_date_before_start_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createValidProject([
                    'startDate' => '2026-01-01',
                    'endDate' => '2025-01-01'
                ]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$ERROR_END_DATE_BEFORE_START, $exception->getMessage());
            }
        );
    }

    // Tests de validación de progress
    public function test_progress_out_of_range_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createValidProject(['progress' => 150]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$ERROR_PROGRESS_INVALID, $exception->getMessage());
            }
        );
    }

    // Tests de validación de presupuesto
    public function test_project_budget_negative_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createValidProject(['projectBudget' => -1000]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$ERROR_PROJECT_BUDGET_INVALID, $exception->getMessage());
            }
        );
    }

    // Tests de validación de ProjectDonors
    public function test_project_donors_not_array_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createValidProject(['projectDonors' => 'not an array']);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$ERROR_PROJECT_DONORS_NOT_ARRAY, $exception->getMessage());
            }
        );
    }

    public function test_project_donors_duplicated_throws_runtime_exception()
    {
        $donor = Donor::at("USAID");
        $duplicatedDonors = [
            ProjectDonor::at($donor, 50),
            ProjectDonor::at($donor, 30) // Mismo donor
        ];

        $this->shouldThrowAndAssert(
            function () use ($duplicatedDonors) {
                $this->createValidProject(['projectDonors' => $duplicatedDonors]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$ERROR_PROJECT_DONORS_DUPLICATED, $exception->getMessage());
            }
        );
    }

    // Tests de validación de entidades
    public function test_invalid_project_beneficiary_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createValidProject(['projectBeneficiary' => 'not a beneficiary']);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$ERROR_PROJECT_BENEFICIARY_INVALID, $exception->getMessage());
            }
        );
    }

    public function test_invalid_contact_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createValidProject(['contact' => 'not a contact']);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$ERROR_CONTACT_INVALID, $exception->getMessage());
            }
        );
    }
}