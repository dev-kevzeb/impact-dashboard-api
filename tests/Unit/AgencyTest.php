<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Agency;
use Exception;
use RuntimeException;

class AgencyTest extends TestCase
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

    public function test_agency_can_be_created_with_valid_data()
    {
        $agency = Agency::at("UNICEF", "https://www.unicef.org", true);

        $this->assertEquals("UNICEF", $agency->getName());
        $this->assertEquals("https://www.unicef.org", $agency->getUrl());
        $this->assertTrue($agency->getIsApproved());
        $this->assertInstanceOf(Agency::class, $agency);
    }



    public function test_agency_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Agency::at("", "https://test.org", true);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el nombre de la agencia no debe ir vacio", $exception->getMessage());
            }
        );
    }

    public function test_agency_name_cannot_be_only_spaces()
    {
        $this->shouldThrowAndAssert(
            function () {
                Agency::at("   ", "https://test.org", true);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el nombre de la agencia no debe ir vacio", $exception->getMessage());
            }
        );
    }

    public function test_agency_name_must_have_minimum_length()
    {
        $this->shouldThrowAndAssert(
            function () {
                Agency::at("A", "https://test.org", true);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el nombre de la agencia debe tener al menos 2 caracteres", $exception->getMessage());
            }
        );
    }

    public function test_agency_name_must_not_exceed_maximum_length()
    {
        $longName = str_repeat("A", 101); // 101 caracteres
        
        $this->shouldThrowAndAssert(
            function () use ($longName) {
                Agency::at($longName, "https://test.org", true);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el nombre de la agencia no debe exceder 100 caracteres", $exception->getMessage());
            }
        );
    }

    public function test_agency_url_cannot_be_empty()
    {
        // URL no puede ser vacía (obligatoria)
        $this->shouldThrowAndAssert(
            function () {
                Agency::at("UNICEF", "", true);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la URL de la agencia no debe ir vacia", $exception->getMessage());
            }
        );
    }

    public function test_agency_url_must_be_valid_when_provided()
    {
        // URL inválida
        $this->shouldThrowAndAssert(
            function () {
                Agency::at("UNICEF", "not-a-url", true);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la URL de la agencia debe tener un formato válido", $exception->getMessage());
            }
        );

        // URL sin protocolo
        $this->shouldThrowAndAssert(
            function () {
                Agency::at("UNICEF", "www.unicef.org", true);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la URL de la agencia debe tener un formato válido", $exception->getMessage());
            }
        );
    }

    public function test_agency_url_must_use_valid_protocol()
    {
        // Protocolo no válido
        $this->shouldThrowAndAssert(
            function () {
                Agency::at("UNICEF", "ftp://unicef.org", true);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("la URL de la agencia debe usar protocolo HTTP o HTTPS", $exception->getMessage());
            }
        );
    }

    public function test_agency_is_approved_must_be_boolean()
    {
        // String en lugar de boolean
        $this->shouldThrowAndAssert(
            function () {
                Agency::at("UNICEF", "https://unicef.org", "true");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el estado de aprobación debe ser un valor booleano", $exception->getMessage());
            }
        );

        // Número en lugar de boolean
        $this->shouldThrowAndAssert(
            function () {
                Agency::at("UNICEF", "https://unicef.org", 1);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals("el estado de aprobación debe ser un valor booleano", $exception->getMessage());
            }
        );
    }

    public function test_agency_with_valid_urls()
    {
        $validUrls = [
            "https://www.unicef.org",
            "http://test.org",
            "https://subdomain.example.com/path",
            "https://unicef.org:8080/api",
        ];

        foreach ($validUrls as $url) {
            $agency = Agency::at("Test Agency", $url, false);
            $this->assertEquals($url, $agency->getUrl());
        }
    }

    public function test_agency_approval_states()
    {
        // Agencia aprobada
        $approvedAgency = Agency::at("UNICEF", "https://unicef.org", true);
        $this->assertTrue($approvedAgency->getIsApproved());
        $this->assertTrue($approvedAgency->isApproved());

        // Agencia no aprobada
        $notApprovedAgency = Agency::at("Test Agency", "https://test.org", false);
        $this->assertFalse($notApprovedAgency->getIsApproved());
        $this->assertFalse($notApprovedAgency->isApproved());
    }

    public function test_agency_name_gets_trimmed()
    {
        $agency = Agency::at("  UNICEF  ", "https://unicef.org", true);
        
        $this->assertEquals("UNICEF", $agency->getName()); // Sin espacios
    }

    public function test_agency_supports_international_characters()
    {
        $agency = Agency::at("Organización Mundial", "https://test.org", true);
        
        $this->assertEquals("Organización Mundial", $agency->getName());
    }



    public function test_compare_is_approved_method()
    {
        $approvedAgency = Agency::at("UNICEF", "https://unicef.org", true);
        $notApprovedAgency = Agency::at("Test", "https://test.org", false);
        
        $this->assertTrue($approvedAgency->compareIsApproved(true));
        $this->assertFalse($approvedAgency->compareIsApproved(false));
        
        $this->assertTrue($notApprovedAgency->compareIsApproved(false));
        $this->assertFalse($notApprovedAgency->compareIsApproved(true));
    }
}
