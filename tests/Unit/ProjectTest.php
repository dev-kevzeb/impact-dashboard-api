<?php

namespace Tests\Unit;

use App\Models\Agency;
use App\Models\Contact;
use PHPUnit\Framework\TestCase;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Indicator;
use App\Models\IndicatorType;
use App\Models\Kpa;
use App\Models\Measure;
use App\Models\Project;
use App\Models\ProjectState;
use App\Models\StrategicOutput;
use DateTimeImmutable;
use Exception;
use RuntimeException;

class ProjectTest extends TestCase
{
    private Project $validProject;
    private Country $validCountry;
    private Measure $validMeasure;
    private StrategicOutput $validStrategicOutput;
    private Kpa $validKpa;
    private Agency $validAgency;
    private Indicator $validIndicator;
    private IndicatorType $validIndicatorType;
    private ProjectState $validProjectState;
    private Contact $validContact;
    private array $validKpas;
    protected function setUp(): void
    {
        parent::setUp();
        $kpa = new Kpa("Nombre KPA", 50, ["Output1", "Output2"]);
        $kpa2 = new Kpa("Nombre KPA 2", 50, ["Output1", "Output2"]);
        $kpa3 = new Kpa("Nombre KPA 3", 50, ["Output1", "Output2"]);
        $this->validKpas = [$kpa, $kpa2, $kpa3];
        $this->validCountry = Country::at("Pais Valido", Currency::at("ARS", "Peso Argentino"), $this->validKpas);
        $this->validAgency = Agency::at("Agencia Valida", "https://www.agencia.com", true);
        $this->validProjectState = ProjectState::at("Estado Valido");
        $this->validContact = Contact::at("NOmbre Valido",  "Apellido valido", "titulo valido", "contacto@ejemplo.com", "123456789");  
        $this->validKpa = Kpa::at("KPA Valido", 50, ["Output1", "Output2"]);
        $this->validStrategicOutput = StrategicOutput::at("Output Valido", $this->validKpa);
        $this->validMeasure = Measure::at("Medida Valida", $this->validStrategicOutput);
        $this->validIndicatorType = IndicatorType::at("Tipo Valido");
        $this->validIndicator = Indicator::at("Indicador Valido", $this->validMeasure, $this->validIndicatorType, 100);
    }
    // bloque de codigo, el manejo de errores, forma en que manejamos el error, "CLOUSURE"
    public function shouldThrowAndAssert($should,$exceptionType,$assertions){
        try {
            $should->__invoke();
            $this->fail();
        } catch (Exception $exception) {
            $this->assertEquals($exceptionType,  get_class($exception));
            $assertions->__invoke($exception);
        }
    }


    public function test_name_empty_string_throws_runtime_exception()
    {
    $country = $this->validCountry;
    $agency = $this->validAgency;
    $indicator = $this->validIndicator;
    $projectState = $this->validProjectState;
    $contact = $this->validContact;
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState, $contact) { $country = Project::at(
            name: "",
            description: "Descripción del proyecto",
            country: $country,
            agency: $agency,
            projectState: $projectState,
            startDate:"2025-02-01",
            endDate:"2026-02-01",
            actualEndDate: "2026-01-15",
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicator: $indicator,
            expectedImpact: 85.5,
            shared: true,
            contact: $contact
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$INVALID_NAME, $exception->getMessage());
            });
    }
    public function test_name_is_string(){
        $country = $this->validCountry;
        $agency = $this->validAgency;
        $indicator = $this->validIndicator;
        $projectState = $this->validProjectState;
        $contact = $this->validContact;
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState, $contact) { $country = Project::at(
            name: 123,
            description: "Descripción del proyecto",
            country: $country,
            agency: $agency,
            projectState: $projectState,
            startDate:"2025-02-01",
            endDate:"2026-02-01",
            actualEndDate: "2026-01-15",
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicator: $indicator,
            expectedImpact: 85.5,
            shared: true,
            contact: $contact
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertIsString($exception->getMessage());
                $this->assertEquals(Project::$INVALID_NAME, $exception->getMessage());
            }
        );
    }
public function test_name_only_whitespace_throws_runtime_exception()
{
    $country = $this->validCountry;
    $agency = $this->validAgency;
    $indicator = $this->validIndicator;
    $projectState = $this->validProjectState;
    $contact = $this->validContact;
    
    $this->shouldThrowAndAssert(
        function () use ($country, $agency, $indicator, $projectState, $contact) {
            Project::at(
                name: "   ",
                description: "Descripción del proyecto",
                country: $country,
                agency: $agency,
                projectState: $projectState,
                startDate: "2025-02-01",
                endDate: "2026-02-01",
                actualEndDate: "2026-01-15",
                budget: 200000.0,
                budgetSpent: 50000.0,
                indicator: $indicator,
                expectedImpact: 85.5,
                shared: true,
                contact: $contact
            );
        },
        RuntimeException::class,
        function ($exception) {
            $this->assertEquals(Project::$INVALID_NAME, $exception->getMessage());
        }
    );
}
    public function test_name_null_or_too_short_throws_runtime_exception()
    {
    $country = $this->validCountry;
    $agency = $this->validAgency;
    $indicator = $this->validIndicator;
    $contact = $this->validContact;

      $projectState = $this->validProjectState;
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState, $contact) { $country = Project::at(
            name: null,
            description: "Descripción del proyecto",
            country: $country,
            agency: $agency,
            projectState: $projectState,
            startDate:"2025-02-01",
            endDate:"2026-02-01",
            actualEndDate: "2026-01-15",
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicator: $indicator,
            expectedImpact: 85.5,
            shared: true,
            contact: $contact
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$INVALID_NAME, $exception->getMessage());
            });
    }
    public function test_description_too_short_throws_runtime_exception()
    {
    $country = $this->validCountry;
    $agency = $this->validAgency;
    $indicator = $this->validIndicator;
    $projectState = $this->validProjectState;
    $contact = $this->validContact;
    $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState, $contact) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "pepe",
            country: $country,
            agency: $agency,
            projectState: $projectState,
            startDate:"2025-02-01",
            endDate:"2026-02-01",
            actualEndDate: "2026-01-15",
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicator: $indicator,
            expectedImpact: 85.5,
            shared: true,
            contact: $contact
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$INVALID_DESCRIPTION, $exception->getMessage());
            });
    }
    public function test_description_null_throws_runtime_exception()
    {
    $country = $this->validCountry;
    $agency = $this->validAgency;
    $indicator = $this->validIndicator;
    $contact = $this->validContact;

      $projectState = $this->validProjectState;
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState, $contact) { $country = Project::at(
            name: "Proyecto de prueba",
            description: null,
            country: $country,
            agency: $agency,
            projectState: $projectState,
            startDate:"2025-02-01",
            endDate:"2026-02-01",
            actualEndDate: "2026-01-15",
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicator: $indicator,
            expectedImpact: 85.5,
            shared: true,
            contact: $contact
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$INVALID_DESCRIPTION, $exception->getMessage());
            });
    }

    public function test_end_date_null_throws_runtime_exception()
    {
    $country = $this->validCountry;
    $agency = $this->validAgency;
    $indicator = $this->validIndicator;
      $projectState = $this->validProjectState;
    $contact = $this->validContact;

        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState, $contact) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            projectState: $projectState,
            startDate:"2025-02-01",
            endDate: null,
            actualEndDate: "2026-01-15",
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicator: $indicator,
            expectedImpact: 85.5,
            shared: true,
            contact: $contact
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$INVALID_DATES, $exception->getMessage());
            });
    }
    public function test_start_date_null_throws_runtime_exception()
    {
    $country = $this->validCountry;
    $agency = $this->validAgency;
    $indicator = $this->validIndicator;
      $projectState = $this->validProjectState;
    $contact = $this->validContact;

        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState, $contact) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            projectState: $projectState,
            startDate: null,
            endDate:"2026-02-01",
            actualEndDate: "2026-01-15",
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicator: $indicator,
            expectedImpact: 85.5,
            shared: true,
            contact: $contact
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$INVALID_DATES, $exception->getMessage());
            });
    }

    public function test_dates_null_validation_throws_runtime_exception()
    {
        $country = $this->validCountry;
        $agency = $this->validAgency;
        $indicator = $this->validIndicator;
        $projectState = $this->validProjectState;
    $contact = $this->validContact;

         $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState, $contact) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            projectState: $projectState,
            startDate:"2025-02-01",
            endDate:"2026-02-01",
            actualEndDate: null,
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicator: $indicator,
            expectedImpact: 85.5,
            shared: true,
            contact: $contact
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$INVALID_DATES, $exception->getMessage());
            });
    }
        public function test_actual_end_date_null_throws_runtime_exception()
    {
        $country = $this->validCountry;
        $agency = $this->validAgency;
        $indicator = $this->validIndicator;
        $projectState = $this->validProjectState;
    $contact = $this->validContact;
        
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState, $contact) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            projectState: $projectState,
            startDate:"2025-02-01",
            endDate:"2026-02-01",
            actualEndDate: null,
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicator: $indicator,
            expectedImpact: 85.5,
            shared: true,
            contact: $contact
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$INVALID_DATES, $exception->getMessage());
            });
    }
    
            public function test_positional_args_end_date_null_throws_runtime_exception()
    {
        $country = $this->validCountry;
        $agency = $this->validAgency;
        $indicator = $this->validIndicator;
  $projectState = $this->validProjectState;
    $contact = $this->validContact;

         $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState, $contact) { $country = Project::at(
            "Proyecto de prueba",
            "descripción valida del proyecto",
            $country,
            $agency,
            $projectState,
           "2025-02-01",
            null,
            "2026-01-15",
            200000.0,
            50000.0,
            $indicator,
            85.5,
            true,
            $contact
        ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$INVALID_DATES, $exception->getMessage());
            });
    }
         public function test_budget_null_or_negative_throws_runtime_exception()
    {
        $country = $this->validCountry;
        $agency = $this->validAgency;
        $indicator = $this->validIndicator;
           $projectState = $this->validProjectState;
    $contact = $this->validContact;
        
           $this->shouldThrowAndAssert(
               function () use ($country, $agency, $indicator, $projectState, $contact) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            projectState: $projectState,
            startDate:"2025-02-01",
            endDate:"2026-02-01",
            actualEndDate: "2026-01-15",
            budget: null,
            budgetSpent: 50000.0,
            indicator: $indicator,
            expectedImpact: 85.5,
            shared: true,
            contact: $contact
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$INVALID_BUDGET, $exception->getMessage());
            });
    }
    public function test_budget_spent_null_or_negative_throws_runtime_exception()
    {
        $country = $this->validCountry;
        $agency = $this->validAgency;
        $indicator = $this->validIndicator;
  $projectState = $this->validProjectState;
    $contact = $this->validContact;

         $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState, $contact) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            projectState: $projectState,
            startDate:"2025-02-01",
            endDate:"2026-02-01",
            actualEndDate: "2026-01-15",
            budget: 200000.0,
            budgetSpent: null,
            indicator: $indicator,
            expectedImpact: 85.5,
            shared: true,
            contact: $contact
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$INVALID_BUDGET_SPENT, $exception->getMessage());
            });
    }
    public function test_indicator_null_or_too_short_throws_runtime_exception()
    {
        $country = $this->validCountry;
        $agency = $this->validAgency;
        $indicator = $this->validIndicator;
          $projectState = $this->validProjectState;
    $contact = $this->validContact;

        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState, $contact) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            projectState: $projectState,
            startDate:"2025-02-01",
            endDate:"2026-02-01",
            actualEndDate: "2026-01-15",
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicator: null,
            expectedImpact: 85.5,
            shared: true,
            contact: $contact
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$INVALID_INDICATOR, $exception->getMessage());
            });
    }
    public function test_expected_impact_null_or_negative_throws_runtime_exception()
    {
        $country = $this->validCountry;
        $agency = $this->validAgency;
        $indicator = $this->validIndicator;
          $projectState = $this->validProjectState;
    $contact = $this->validContact;

        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState, $contact) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            projectState: $projectState,
            startDate:"2025-02-01",
            endDate:"2026-02-01",
            actualEndDate: "2026-01-15",
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicator: $indicator,
            expectedImpact: null,
            shared: true,
            contact: $contact
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$INVALID_EXPECTED_IMPACT, $exception->getMessage());
            });
    }
    public function test_manager_null_throws_runtime_exception()
    {
        $country = $this->validCountry;
        $agency = $this->validAgency;
        $indicator = $this->validIndicator;
        $projectState = $this->validProjectState;
    $contact = $this->validContact;
         
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState, $contact) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            projectState: $projectState,
            startDate:"2025-02-01",
            endDate:"2026-02-01",
            actualEndDate: "2026-01-15",
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicator: $indicator,
            expectedImpact: 85.5,
            shared: true,
            contact: null
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$INVALID_CONTACT, $exception->getMessage());
            });
    }
    public function test_shared_null_throws_runtime_exception()
    {
        $country = $this->validCountry;
        $agency = $this->validAgency;
        $indicator = $this->validIndicator;
        $projectState = $this->validProjectState;
        $contact = $this->validContact;

        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState, $contact) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            projectState: $projectState,
            startDate:"2025-02-01",
            endDate:"2026-02-01",
            actualEndDate: "2026-01-15",
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicator: $indicator,
            expectedImpact: 85.5,
            shared: null,
            contact: $contact
        ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Project::$INVALID_SHARED, $exception->getMessage());
            });
    }


    

}
