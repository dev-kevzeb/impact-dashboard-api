<?php

namespace Tests\Unit;

use App\Models\Agency;
use PHPUnit\Framework\TestCase;
use App\Models\Country;
use App\Models\Project;
use DateTimeImmutable;
use Exception;
use RuntimeException;

class ProjectTest extends TestCase
{
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
        $type = \App\Models\IndicatorType::at('tipo');
        return \App\Models\Indicator::at('Indicador principal','unidad',$type);
    }



    public function test_name_empty_string_throws_runtime_exception()
    {
    $country = Country::at("Argentina");
    $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
    $indicator = $this->makeIndicator();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator) { $country = Project::at(
            name: "",
            description: "Descripción del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
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
    public function test_name_null_or_too_short_throws_runtime_exception()
    {
    $country = Country::at("Argentina");
    $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
    $indicator = $this->makeIndicator();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator) { $country = Project::at(
            name: null,
            description: "Descripción del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
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
    $country = Country::at("Argentina");
    $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
    $indicator = $this->makeIndicator();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "pepe",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
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
    $country = Country::at("Argentina");
    $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
    $indicator = $this->makeIndicator();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator) { $country = Project::at(
            name: "Proyecto de prueba",
            description: null,
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
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
    public function test_description_null_throws_runtime_exception_duplicate()
    {
    $country = Country::at("Argentina");
    $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
    $indicator = $this->makeIndicator();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator) { $country = Project::at(
            name: "Proyecto de prueba",
            description: null,
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
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
    $country = Country::at("Argentina");
    $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
    $indicator = $this->makeIndicator();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: null,
            actualEndDate: new DateTimeImmutable("2026-01-15"),
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
    $country = Country::at("Argentina");
    $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
    $indicator = $this->makeIndicator();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: null,
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
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
        $country = Country::at("Argentina");
        $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
        $indicator = $this->makeIndicator();
         $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
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
        $country = Country::at("Argentina");
        $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
        $indicator = $this->makeIndicator();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
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
        $country = Country::at("Argentina");
        $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
        $indicator = $this->makeIndicator();

         $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator) { $country = Project::at(
            "Proyecto de prueba",
            "descripción valida del proyecto",
            $country,
            $agency,
            "En ejecución",
            new DateTimeImmutable("2025-02-01"),
            null,
            new DateTimeImmutable("2026-01-15"),
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
        $country = Country::at("Argentina");
        $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
        $indicator = $this->makeIndicator();
         
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
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
        $country = Country::at("Argentina");
        $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
        $indicator = $this->makeIndicator();

         $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
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
        $country = Country::at("Argentina");
        $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
        $indicator = $this->makeIndicator();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
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
        $country = Country::at("Argentina");
        $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
        $indicator = $this->makeIndicator();
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
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
        $country = Country::at("Argentina");
        $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
        $indicator = $this->makeIndicator();
         $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
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
        $country = Country::at("Argentina");
        $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
        $indicator = $this->makeIndicator(); 
        $this->shouldThrowAndAssert(
            function () use ($country, $agency, $indicator) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
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
