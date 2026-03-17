<?php

namespace Tests\Unit\Project;


use App\Modules\Project\Domain\Project;
use App\Modules\Agency\Domain\Agency;
use App\Modules\Beneficiary\Domain\Beneficiary;
use App\Modules\Contact\Domain\Contact;
use App\Modules\Country\Domain\Country;
use App\Modules\Currency\Domain\Currency;
use App\Modules\Donor\Domain\Donor;
use App\Modules\Indicator\Domain\Indicator;
use App\Modules\IndicatorType\Domain\IndicatorType;
use App\Modules\Measure\Domain\Measure;
use App\Modules\ProjectState\Domain\ProjectState;
use App\Modules\StrategicOutput\Domain\StrategicOutput;
use Exception;
use RuntimeException;
use PHPUnit\Framework\TestCase;

class ProjectTest extends TestCase
{
    private Country $validCountry;
    private Agency $validAgency;
    private Indicator $validIndicator;
    private ProjectState $validProjectState;
    private Contact $validContact;
    private Beneficiary $validProjectBeneficiary;
    private array $validDonors;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->validCountry = Country::at("Pais Valido", Currency::at("ARS"));
        $this->validAgency = Agency::at("Agencia Valida", "https://www.agencia.com", true);
        $this->validProjectState = ProjectState::at("Estado Valido");
        $this->validContact = Contact::at("Nombre Valido", "Apellido valido", "titulo valido", "contacto@ejemplo.com", "123456789");  
        $this->validProjectBeneficiary = Beneficiary::at("GOVERNMENT");
        
        // Crear KPA hierarchy para Indicator
        //$validKpa = Kpa::at("KPA Valido", 50);
        //$countryKpa = CountryKpa::at("");
        $validStrategicOutput = StrategicOutput::at("Output Valido");
        $validMeasure = Measure::at("Medida Valida", $validStrategicOutput);
        $validIndicatorType = IndicatorType::at("Tipo Valido");
        $this->validIndicator = Indicator::at("Indicador Valido", $validIndicatorType,100, $validMeasure);
        
        $donor1 = Donor::at("USAID");
        $donor2 = Donor::at("World Bank");
        // Crear ProjectDonors válidos
        $this->validDonors = [
            $donor1, $donor2
        ];
    }
    
    private function createValidProject(array $overrides = []): Project
    {
        $defaults = [
            'program_id' => 1,
            'name' => 'Proyecto de Desarrollo Rural',
            'description' => 'Descripción válida del proyecto de desarrollo rural con más de 10 caracteres',
            'projectUrl' => 'https://proyecto.example.com',
            'startDate' => '2025-01-01',
            'endDate' => '2026-12-31',
            'progress' => 75.5,
            'comments' => 'Comentarios del proyecto en progreso',
            'projectBudget' => 500000.0,
            'weight' => 0.5,
            'contact' => $this->validContact,
            'projectBeneficiary' => $this->validProjectBeneficiary,
            'projectState' => $this->validProjectState,
        ];

        $p = array_merge($defaults, $overrides);
        return Project::at(
            $p['program_id'],
            $p['name'],
            $p['description'],
            $p['projectUrl'],
            $p['startDate'],
            $p['endDate'],
            $p['progress'],
            $p['comments'],
            $p['projectBudget'],
            $p['weight'],
            $p['contact'],
            $p['projectBeneficiary'],
            $p['projectState']
        );
    }
    
    // bloque de código, el manejo de errores, forma en que manejamos el error, "CLOSURE"
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

    // Tests básicos de creación exitosa


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

    // Tests de validación de weight

    public function test_weight_below_zero_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createValidProject(['weight' => -0.1]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$ERROR_WEIGHT_INVALID, $exception->getMessage());
            }
        );
    }

    public function test_weight_above_one_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createValidProject(['weight' => 1.1]);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$ERROR_WEIGHT_INVALID, $exception->getMessage());
            }
        );
    }

    public function test_weight_not_numeric_throws_runtime_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                $this->createValidProject(['weight' => 'heavy']);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$ERROR_WEIGHT_INVALID, $exception->getMessage());
            }
        );
    }

    public function test_weight_zero_is_valid()
    {
        $project = $this->createValidProject(['weight' => 0]);
        $this->assertEquals(0.0, (float) $project->weight);
    }

    public function test_weight_one_is_valid()
    {
        $project = $this->createValidProject(['weight' => 1]);
        $this->assertEquals(1.0, (float) $project->weight);
    }

    public function test_weight_decimal_is_stored()
    {
        $project = $this->createValidProject(['weight' => 0.3333]);
        $this->assertEquals(0.3333, (float) $project->weight);
    }

}