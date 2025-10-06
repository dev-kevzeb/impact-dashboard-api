<?php

namespace Tests\Unit;

use App\Models\Agency;
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
    private ProjectState $validProjectState;
    protected function setUp(): void
    {
        parent::setUp();
        $this->validKpa = Kpa::at("KPA Valido");
        $this->validStrategicOutput = StrategicOutput::at("Output Valido", $this->validKpa);
        $this->validMeasure = new Measure("Medida Valida", $this->validStrategicOutput);

        $this->validIndicator = Indicator::at(
            name: "Indicador Valido",
            measure: "Medida Valida",
            type: IndicatorType::at("Tipo Valido"),
            target: 100
        );


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

    private function makeIndicator()
    {
        $type = IndicatorType::at('tipo');
        return Indicator::at('Indicador principal','unidad',$type, 1);
    }
    
    private function makeCountry()
    {
        $currency = Currency::at("ARS", "Peso Argentino");
        return Country::at("Argentina", $currency);
    }
    private function makeAgency()
    {
        return Agency::at("Agencia de prueba", "https://www.anh.gob.bo", true); 
    }
    private function makeProjectState()
    {
        return ProjectState::at("En ejecucion");
    }


    public function test_name_empty_string_throws_runtime_exception()
    {
    $country = $this->makeCountry();
    $agency =$this->makeAgency();
    $indicator = $this->makeIndicator();
    $projectState = $this->makeProjectState();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState) { $country = Project::at(
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
            manager: "María Pérez",
            shared: true
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'el nombre del proyecto no debe ser null');
            });
    }
    public function test_name_is_string(){
        $country = $this->makeCountry();
        $agency =$this->makeAgency();
        $indicator = $this->makeIndicator();
        $projectState = $this->makeProjectState();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState) { $country = Project::at(
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
            manager: "María Pérez",
            shared: true
            ); },
            RuntimeException::class,
            function ($exception) {
                 $this->assertIsString($exception->getMessage());
                $this->assertEquals(
                    'el nombre del proyecto no debe tener unicamente numeros',
                    $exception->getMessage()
                );
            }
        );
    }
public function test_name_only_whitespace_throws_runtime_exception()
{
    $country = $this->makeCountry();
    $agency = $this->makeAgency();
    $indicator = $this->makeIndicator();
    $projectState = $this->makeProjectState();
    
    $this->shouldThrowAndAssert(
        function () use ($country, $agency, $indicator, $projectState) {
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
                manager: "María Pérez",
                shared: true
            );
        },
        RuntimeException::class,
        function ($exception) {
            $this->assertEquals('el nombre del proyecto no debe ser null o menor a 3 caracteres', $exception->getMessage());
        }
    );
}
    public function test_name_null_or_too_short_throws_runtime_exception()
    {
    $country = $this->makeCountry();
    $agency =$this->makeAgency();
    $indicator = $this->makeIndicator();
      $projectState = $this->makeProjectState();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState) { $country = Project::at(
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
            manager: "María Pérez",
            shared: true
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'el nombre del proyecto no debe ser null o menor a 3 caracteres');
            });
    }
    public function test_description_too_short_throws_runtime_exception()
    {
    $country = $this->makeCountry();
    $agency =$this->makeAgency();
    $indicator = $this->makeIndicator();
      $projectState = $this->makeProjectState();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState) { $country = Project::at(
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
            manager: "María Pérez",
            shared: true
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'la descripcion del proyecto no debe ser null o menor a 10 caracteres');
            });
    }
    public function test_description_null_throws_runtime_exception()
    {
    $country = $this->makeCountry();
    $agency =$this->makeAgency();
    $indicator = $this->makeIndicator();
      $projectState = $this->makeProjectState();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState) { $country = Project::at(
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
            manager: "María Pérez",
            shared: true
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'la descripcion del proyecto no debe ser null o menor a 10 caracteres');
            });
    }

    public function test_end_date_null_throws_runtime_exception()
    {
    $country = $this->makeCountry();
    $agency =$this->makeAgency();
    $indicator = $this->makeIndicator();
      $projectState = $this->makeProjectState();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState) { $country = Project::at(
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
            manager: "María Pérez",
            shared: true
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'Las fechas no deben ser null');
            });
    }
    public function test_start_date_null_throws_runtime_exception()
    {
    $country = $this->makeCountry();
    $agency =$this->makeAgency();
    $indicator = $this->makeIndicator();
      $projectState = $this->makeProjectState();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState) { $country = Project::at(
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
            manager: "María Pérez",
            shared: true
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'Las fechas no deben ser null');
            });
    }

    public function test_dates_null_validation_throws_runtime_exception()
    {
        $country = $this->makeCountry();
        $agency =$this->makeAgency();
        $indicator = $this->makeIndicator();
        $projectState = $this->makeProjectState();
         $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState) { $country = Project::at(
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
            manager: "María Pérez",
            shared: true
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'Las fechas no deben ser null');
            });
    }
        public function test_actual_end_date_null_throws_runtime_exception()
    {
        $country = $this->makeCountry();
        $agency =$this->makeAgency();
        $indicator = $this->makeIndicator();
        $projectState = $this->makeProjectState();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState) { $country = Project::at(
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
            manager: "María Pérez",
            shared: true
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'Las fechas no deben ser null');
            });
    }
    
            public function test_positional_args_end_date_null_throws_runtime_exception()
    {
        $country = $this->makeCountry();
        $agency =$this->makeAgency();
        $indicator = $this->makeIndicator();
  $projectState = $this->makeProjectState();
         $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState) { $country = Project::at(
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
            "María Pérez",
            true
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'Las fechas no deben ser null');
            });
    }
         public function test_budget_null_or_negative_throws_runtime_exception()
    {
        $country = $this->makeCountry();
        $agency =$this->makeAgency();
        $indicator = $this->makeIndicator();
           $projectState = $this->makeProjectState();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState) { $country = Project::at(
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
            manager: "María Pérez",
            shared: true
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'El presupuesto total no debe ser null o menor a 0');
            });
    }
    public function test_budget_spent_null_or_negative_throws_runtime_exception()
    {
        $country = $this->makeCountry();
        $agency =$this->makeAgency();
        $indicator = $this->makeIndicator();
  $projectState = $this->makeProjectState();
         $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState) { $country = Project::at(
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
            manager: "María Pérez",
            shared: true
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'El proyecto debe tener un manager asignado');
            });
    }
    public function test_indicator_null_or_too_short_throws_runtime_exception()
    {
        $country = $this->makeCountry();
        $agency =$this->makeAgency();
        $indicator = $this->makeIndicator();
          $projectState = $this->makeProjectState();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState) { $country = Project::at(
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
            manager: "María Pérez",
            shared: true
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'El indicador no debe ser null o menor a 3 caracteres');
            });
    }
    public function test_expected_impact_null_or_negative_throws_runtime_exception()
    {
        $country = $this->makeCountry();
        $agency =$this->makeAgency();
        $indicator = $this->makeIndicator();
          $projectState = $this->makeProjectState();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState) { $country = Project::at(
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
            manager: "María Pérez",
            shared: true
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'el expectedImpact no debe ser null o menor a 0');
            });
    }
    public function test_manager_null_throws_runtime_exception()
    {
        $country = $this->makeCountry();
        $agency =$this->makeAgency();
        $indicator = $this->makeIndicator();
        $projectState = $this->makeProjectState();
         $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState) { $country = Project::at(
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
            manager: null,
            shared: true
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'El proyecto debe tener un manager asignado');
            });
    }
    public function test_shared_null_throws_runtime_exception()
    {
        $country = $this->makeCountry();
        $agency =$this->makeAgency();
        $indicator = $this->makeIndicator();
        $projectState = $this->makeProjectState(); 
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator, $projectState) { $country = Project::at(
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
            manager: "María Pérez",
            shared: null
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'shared no debe ser null o vacio');
            });
    }


    

}
