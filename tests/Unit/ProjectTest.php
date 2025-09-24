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



    public function test_name_empty_string_throws_runtime_exception()
    {
        $country = Country::at("Argentina");
        $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
         $this->shouldThrowAndAssert(
            function () use ($country, $agency) { $country = Project::at(
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
            indicators: "Indicadores del proyecto",
            expectedImpact: 85.5,
            documents: "plan_inicial.pdf, informe_avance.pdf",
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
         $this->shouldThrowAndAssert(
            function () use ($country, $agency) { $country = Project::at(
            name: NULL,
            description: "Descripción del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicators: "Indicadores del proyecto",
            expectedImpact: 85.5,
            documents: "plan_inicial.pdf, informe_avance.pdf",
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
         $this->shouldThrowAndAssert(
            function () use ($country, $agency) { $country = Project::at(
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
            indicators: "Indicadores del proyecto",
            expectedImpact: 85.5,
            documents: "plan_inicial.pdf, informe_avance.pdf",
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
         $this->shouldThrowAndAssert(
            function () use ($country, $agency) { $country = Project::at(
            name: "Proyecto de prueba",
            description: NULL,
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicators: "Indicadores del proyecto",
            expectedImpact: 85.5,
            documents: "plan_inicial.pdf, informe_avance.pdf",
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
         $this->shouldThrowAndAssert(
            function () use ($country, $agency) { $country = Project::at(
            name: "Proyecto de prueba",
            description: NULL,
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicators: "Indicadores del proyecto",
            expectedImpact: 85.5,
            documents: "plan_inicial.pdf, informe_avance.pdf",
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
         $this->shouldThrowAndAssert(
            function () use ($country, $agency) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: NULL,
            actualEndDate: new DateTimeImmutable("2026-01-15"),
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicators: "Indicadores del proyecto",
            expectedImpact: 85.5,
            documents: "plan_inicial.pdf, informe_avance.pdf",
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
         $this->shouldThrowAndAssert(
            function () use ($country, $agency) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: NULL,
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicators: "Indicadores del proyecto",
            expectedImpact: 85.5,
            documents: "plan_inicial.pdf, informe_avance.pdf",
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
         $this->shouldThrowAndAssert(
            function () use ($country, $agency) { $country = Project::at(
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
            indicators: "Indicadores del proyecto",
            expectedImpact: 85.5,
            documents: "plan_inicial.pdf, informe_avance.pdf",
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
         $this->shouldThrowAndAssert(
            function () use ($country, $agency) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: NULL,
            budget: 200000.0,
            budgetSpent: 50000.0,
            indicators: "Indicadores del proyecto",
            expectedImpact: 85.5,
            documents: "plan_inicial.pdf, informe_avance.pdf",
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
         $this->shouldThrowAndAssert(
            function () use ($country, $agency) { $country = Project::at(
            "Proyecto de prueba",
            "descripción valida del proyecto",
            $country,
            $agency,
            "En ejecución",
            new DateTimeImmutable("2025-02-01"),
            NULL,
            new DateTimeImmutable("2026-01-15"),
            200000.0,
            50000.0,
            "Indicadores del proyecto",
            85.5,
            "plan_inicial.pdf, informe_avance.pdf",
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
         $this->shouldThrowAndAssert(
            function () use ($country, $agency) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
            budget: NULL,
            budgetSpent: 50000.0,
            indicators: "Indicadores del proyecto",
            expectedImpact: 85.5,
            documents: "plan_inicial.pdf, informe_avance.pdf",
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
         $this->shouldThrowAndAssert(
            function () use ($country, $agency) { $country = Project::at(
            name: "Proyecto de prueba",
            description: "descripción valida del proyecto",
            country: $country,
            agency: $agency,
            state: "En ejecución",
            startDate: new DateTimeImmutable("2025-02-01"),
            endDate: new DateTimeImmutable("2026-02-01"),
            actualEndDate: new DateTimeImmutable("2026-01-15"),
            budget: 200000.0,
            budgetSpent: NULL,
            indicators: "Indicadores del proyecto",
            expectedImpact: 85.5,
            documents: "plan_inicial.pdf, informe_avance.pdf",
            manager: "María Pérez",
            shared: true
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'el presupuesto gastado no debe ser null o menor a 0');
            });
    }
    public function test_indicators_null_or_too_short_throws_runtime_exception()
    {
        $country = Country::at("Argentina");
        $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
         $this->shouldThrowAndAssert(
            function () use ($country, $agency) { $country = Project::at(
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
            indicators: NULL,
            expectedImpact: 85.5,
            documents: "plan_inicial.pdf, informe_avance.pdf",
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
         $this->shouldThrowAndAssert(
            function () use ($country, $agency) { $country = Project::at(
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
            indicators: "indicador 1",
            expectedImpact: NULL,
            documents: "plan_inicial.pdf, informe_avance.pdf",
            manager: "María Pérez",
            shared: true
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'el expectedImpact no debe ser null o menor a 0');
            });
    }
            public function test_documents_empty_string_throws_runtime_exception()
    {
        $country = Country::at("Argentina");
        $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
         $this->shouldThrowAndAssert(
            function () use ($country, $agency) { $country = Project::at(
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
            indicators: "indicador 1",
            expectedImpact: 85.5,
            documents: "",
            manager: "María Pérez",
            shared: true
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'documents no debe ser null o vacio');
            });
    }
            public function test_manager_null_throws_runtime_exception()
    {
        $country = Country::at("Argentina");
        $agency = Agency::at("Agencia de prueba", "http://agencia1.com", true);
         $this->shouldThrowAndAssert(
            function () use ($country, $agency) { $country = Project::at(
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
            indicators: "indicador 1",
            expectedImpact: 85.5,
            documents: "plan_inicial.pdf, informe_avance.pdf",
            manager: NULL,
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
         $this->shouldThrowAndAssert(
            function () use ($country, $agency) { $country = Project::at(
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
            indicators: "indicador 1",
            expectedImpact: 85.5,
            documents: "plan_inicial.pdf, informe_avance.pdf",
            manager: "María Pérez",
            shared: NULL
            ); },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals($exception->getMessage(),'shared no debe ser null o vacio');
            });
    }
}
