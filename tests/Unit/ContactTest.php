<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Contact;
use Exception;
use RuntimeException;

class ContactTest extends TestCase
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

    public function test_contact_can_be_created_with_valid_data()
    {
        $contact = Contact::at("Juan", "Pérez", "Director", "juan.perez@email.com", "+591 70123456");

        $this->assertEquals("Juan", $contact->getFirstName());
        $this->assertEquals("Pérez", $contact->getLastName());
        $this->assertEquals("Director", $contact->getTitle());
        $this->assertEquals("juan.perez@email.com", $contact->getEmail());
        $this->assertEquals("+591 70123456", $contact->getPhone());
        $this->assertEquals("Juan Pérez", $contact->getFullName());
        $this->assertTrue($contact->hasPhone());
        $this->assertInstanceOf(Contact::class, $contact);
    }

    public function test_contact_can_be_created_without_phone()
    {
        $contact = Contact::at("Ana", "García", "Coordinadora", "ana.garcia@email.com");

        $this->assertEquals("Ana", $contact->getFirstName());
        $this->assertEquals("García", $contact->getLastName());
        $this->assertEquals("Coordinadora", $contact->getTitle());
        $this->assertEquals("ana.garcia@email.com", $contact->getEmail());
        $this->assertEquals("", $contact->getPhone());
        $this->assertEquals("Ana García", $contact->getFullName());
        $this->assertFalse($contact->hasPhone());
        $this->assertInstanceOf(Contact::class, $contact);
    }

    public function test_first_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Contact::at("", "Pérez", "Director", "test@email.com");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Contact::$ERROR_FIRST_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_first_name_cannot_be_only_spaces()
    {
        $this->shouldThrowAndAssert(
            function () {
                Contact::at("   ", "Pérez", "Director", "test@email.com");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Contact::$ERROR_FIRST_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_first_name_must_have_minimum_length()
    {
        $this->shouldThrowAndAssert(
            function () {
                Contact::at("A", "Pérez", "Director", "test@email.com");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Contact::$ERROR_FIRST_NAME_MIN_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_first_name_must_not_exceed_maximum_length()
    {
        $longName = str_repeat("A", 51);
        
        $this->shouldThrowAndAssert(
            function () use ($longName) {
                Contact::at($longName, "Pérez", "Director", "test@email.com");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Contact::$ERROR_FIRST_NAME_MAX_LENGTH, $exception->getMessage());
            }
        );
    }

    public function test_first_name_with_invalid_characters_throws_error()
    {
        $invalidNames = ["Juan123", "Ana@email", "Pedro#Test", "Luis\$Money"];
        
        foreach ($invalidNames as $name) {
            try {
                Contact::at($name, "Pérez", "Director", "test@email.com");
                $this->fail("Debería haber lanzado excepción para: {$name}");
            } catch (RuntimeException $exception) {
                $this->assertEquals(Contact::$ERROR_FIRST_NAME_INVALID_CHARS, $exception->getMessage());
            }
        }
    }

    public function test_first_name_with_valid_special_characters()
    {
        $validNames = ["María", "José-Luis", "Ana Sofía", "O'Connor", "François"];
        
        foreach ($validNames as $name) {
            $contact = Contact::at($name, "Pérez", "Director", "test@email.com");
            $this->assertEquals($name, $contact->getFirstName());
            $this->assertInstanceOf(Contact::class, $contact);
        }
    }

    public function test_last_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Contact::at("Juan", "", "Director", "test@email.com");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Contact::$ERROR_LAST_NAME_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_title_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Contact::at("Juan", "Pérez", "", "test@email.com");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Contact::$ERROR_TITLE_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_title_with_valid_characters()
    {
        $validTitles = [
            "Director Ejecutivo",
            "Coordinador de Proyectos",
            "Gerente (Operaciones)",
            "CEO - Chief Executive Officer",
            "Especialista en TI",
            "Consultor Sr."
        ];
        
        foreach ($validTitles as $title) {
            $contact = Contact::at("Juan", "Pérez", $title, "test@email.com");
            $this->assertEquals($title, $contact->getTitle());
            $this->assertInstanceOf(Contact::class, $contact);
        }
    }

    public function test_email_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            function () {
                Contact::at("Juan", "Pérez", "Director", "");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Contact::$ERROR_EMAIL_EMPTY, $exception->getMessage());
            }
        );
    }

    public function test_email_must_have_valid_format()
    {
        $invalidEmails = ["invalid-email", "test@", "@domain.com", "test.domain.com", "test@domain"];
        
        foreach ($invalidEmails as $email) {
            $this->shouldThrowAndAssert(
                function () use ($email) {
                    Contact::at("Juan", "Pérez", "Director", $email);
                },
                RuntimeException::class,
                function ($exception) {
                    $this->assertEquals(Contact::$ERROR_EMAIL_INVALID_FORMAT, $exception->getMessage());
                }
            );
        }
    }

    public function test_email_with_valid_formats()
    {
        $validEmails = [
            "test@example.com",
            "user.name@domain.org",
            "user+tag@example.co.uk",
            "123@numbers.com"
        ];
        
        foreach ($validEmails as $email) {
            $contact = Contact::at("Juan", "Pérez", "Director", $email);
            $this->assertEquals($email, $contact->getEmail());
            $this->assertInstanceOf(Contact::class, $contact);
        }
    }

    public function test_phone_with_valid_formats()
    {
        $validPhones = [
            "+591 70123456",
            "591-70123456",
            "(591) 70123456",
            "70123456",
            "+1 555 123 4567"
        ];
        
        foreach ($validPhones as $phone) {
            $contact = Contact::at("Juan", "Pérez", "Director", "test@email.com", $phone);
            $this->assertEquals($phone, $contact->getPhone());
            $this->assertTrue($contact->hasPhone());
            $this->assertInstanceOf(Contact::class, $contact);
        }
    }

    public function test_phone_with_invalid_characters_throws_error()
    {
        $invalidPhones = ["123abc456", "phone123", "123-abc-456"];
        
        foreach ($invalidPhones as $phone) {
            $this->shouldThrowAndAssert(
                function () use ($phone) {
                    Contact::at("Juan", "Pérez", "Director", "test@email.com", $phone);
                },
                RuntimeException::class,
                function ($exception) {
                    $this->assertEquals(Contact::$ERROR_PHONE_INVALID_FORMAT, $exception->getMessage());
                }
            );
        }
    }

    public function test_phone_too_short_throws_error()
    {
        $this->shouldThrowAndAssert(
            function () {
                Contact::at("Juan", "Pérez", "Director", "test@email.com", "123456");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Contact::$ERROR_PHONE_TOO_SHORT, $exception->getMessage());
            }
        );
    }

    public function test_phone_too_long_throws_error()
    {
        $longPhone = "1234567890123456"; // 16 dígitos
        
        $this->shouldThrowAndAssert(
            function () use ($longPhone) {
                Contact::at("Juan", "Pérez", "Director", "test@email.com", $longPhone);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(Contact::$ERROR_PHONE_TOO_LONG, $exception->getMessage());
            }
        );
    }

    public function test_contact_trims_whitespace()
    {
        $contact = Contact::at("  Juan  ", "  Pérez  ", "  Director  ", "  test@email.com  ", "  70123456  ");
        
        $this->assertEquals("Juan", $contact->getFirstName());
        $this->assertEquals("Pérez", $contact->getLastName());
        $this->assertEquals("Director", $contact->getTitle());
        $this->assertEquals("test@email.com", $contact->getEmail());
        $this->assertEquals("70123456", $contact->getPhone());
    }
}