<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Contact extends Model
{
    // Constantes de mensajes de error
    public static $ERROR_FIRST_NAME_EMPTY = 'el nombre del contacto no debe ir vacío';
    public static $ERROR_FIRST_NAME_MIN_LENGTH = 'el nombre del contacto debe tener al menos 2 caracteres';
    public static $ERROR_FIRST_NAME_MAX_LENGTH = 'el nombre del contacto no debe exceder 50 caracteres';
    public static $ERROR_FIRST_NAME_INVALID_CHARS = 'el nombre del contacto contiene caracteres no válidos';
    public static $ERROR_LAST_NAME_EMPTY = 'el apellido del contacto no debe ir vacío';
    public static $ERROR_LAST_NAME_MIN_LENGTH = 'el apellido del contacto debe tener al menos 2 caracteres';
    public static $ERROR_LAST_NAME_MAX_LENGTH = 'el apellido del contacto no debe exceder 50 caracteres';
    public static $ERROR_LAST_NAME_INVALID_CHARS = 'el apellido del contacto contiene caracteres no válidos';
    public static $ERROR_TITLE_EMPTY = 'el título del contacto no debe ir vacío';
    public static $ERROR_TITLE_MIN_LENGTH = 'el título del contacto debe tener al menos 2 caracteres';
    public static $ERROR_TITLE_MAX_LENGTH = 'el título del contacto no debe exceder 100 caracteres';
    public static $ERROR_TITLE_INVALID_CHARS = 'el título del contacto contiene caracteres no válidos';
    public static $ERROR_EMAIL_EMPTY = 'el email del contacto no debe ir vacío';
    public static $ERROR_EMAIL_INVALID_FORMAT = 'el email del contacto debe tener un formato válido';
    public static $ERROR_EMAIL_TOO_LONG = 'el email del contacto excede la longitud máxima permitida (254 caracteres)';
    public static $ERROR_EMAIL_INVALID_DOMAIN = 'el dominio del email no es válido o no existe';
    public static $ERROR_PHONE_INVALID_FORMAT = 'el formato del teléfono no es válido - use formato internacional';
    public static $ERROR_PHONE_TOO_SHORT = 'el teléfono debe tener al menos 7 dígitos';
    public static $ERROR_PHONE_TOO_LONG = 'el teléfono no debe exceder 15 dígitos';

    private string $firstName;
    private string $lastName;
    private string $title;
    private string $email;
    private string $phone;

    public function __construct(string $firstName, string $lastName, string $title, string $email, string $phone)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->title = $title;
        $this->email = $email;
        $this->phone = $phone;
    }

    public static function at(string $firstName, string $lastName, string $title, string $email, string $phone = ""): Contact
    {
        // Validar firstName
        if (empty(trim($firstName))) {
            throw new RuntimeException(self::$ERROR_FIRST_NAME_EMPTY);
        }
        
        $trimmedFirstName = trim($firstName);
        
        if (strlen($trimmedFirstName) < 2) {
            throw new RuntimeException(self::$ERROR_FIRST_NAME_MIN_LENGTH);
        }
        
        if (strlen($trimmedFirstName) > 50) {
            throw new RuntimeException(self::$ERROR_FIRST_NAME_MAX_LENGTH);
        }
        
        if (!self::hasValidTextCharacters($trimmedFirstName)) {
            throw new RuntimeException(self::$ERROR_FIRST_NAME_INVALID_CHARS);
        }

        // Validar lastName
        if (empty(trim($lastName))) {
            throw new RuntimeException(self::$ERROR_LAST_NAME_EMPTY);
        }
        
        $trimmedLastName = trim($lastName);
        
        if (strlen($trimmedLastName) < 2) {
            throw new RuntimeException(self::$ERROR_LAST_NAME_MIN_LENGTH);
        }
        
        if (strlen($trimmedLastName) > 50) {
            throw new RuntimeException(self::$ERROR_LAST_NAME_MAX_LENGTH);
        }
        
        if (!self::hasValidTextCharacters($trimmedLastName)) {
            throw new RuntimeException(self::$ERROR_LAST_NAME_INVALID_CHARS);
        }

        // Validar title
        if (empty(trim($title))) {
            throw new RuntimeException(self::$ERROR_TITLE_EMPTY);
        }
        
        $trimmedTitle = trim($title);
        
        if (strlen($trimmedTitle) < 2) {
            throw new RuntimeException(self::$ERROR_TITLE_MIN_LENGTH);
        }
        
        if (strlen($trimmedTitle) > 100) {
            throw new RuntimeException(self::$ERROR_TITLE_MAX_LENGTH);
        }
        
        if (!self::hasValidTextCharacters($trimmedTitle)) {
            throw new RuntimeException(self::$ERROR_TITLE_INVALID_CHARS);
        }

        // Validar email
        if (empty(trim($email))) {
            throw new RuntimeException(self::$ERROR_EMAIL_EMPTY);
        }
        
        $trimmedEmail = trim($email);
        
        if (!self::isValidEmail($trimmedEmail)) {
        }

        // Validar phone (opcional)
        $trimmedPhone = trim($phone);
        if (!empty($trimmedPhone)) {
            if (!self::isValidPhoneFormat($trimmedPhone)) {
                throw new RuntimeException(self::$ERROR_PHONE_INVALID_FORMAT);
            }
        }

        return new Contact($trimmedFirstName, $trimmedLastName, $trimmedTitle, $trimmedEmail, $trimmedPhone);
    }

    private static function hasValidTextCharacters(string $text): bool
    {
        // Letras, espacios, acentos, guiones, apostrofes, ñÑ y puntuación profesional
        return preg_match('/^[a-zA-ZÀ-ÿñÑ\s\'-\.,\/]+$/u', $text);
    }

    private static function isValidPhoneFormat(string $phone): bool
    {
        // Acepta formatos: +591 70123456, 591-70123456, (591) 70123456, 70123456, +1 555 123 4567
        if (!preg_match('/^(\+?\d{1,4})?[\s\-]?\(?\d{1,4}\)?[\s\-]?\d+(\s?\-?\d+)*$/', $phone)) {
            return false;
        }
        
        // Validar longitud total de dígitos
        $digitsOnly = preg_replace('/\D/', '', $phone);
        $length = strlen($digitsOnly);
        
        if ($length < 7) {
            throw new RuntimeException(self::$ERROR_PHONE_TOO_SHORT);
        }
        
        if ($length > 15) {
            throw new RuntimeException(self::$ERROR_PHONE_TOO_LONG);
        }
        
        return true;
    }

    private static function isValidEmail(string $email): bool
    {
        // Normalizar email a minúsculas
        $email = strtolower($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE)) {
            throw new RuntimeException(self::$ERROR_EMAIL_INVALID_FORMAT);
        }
        
        // RFC 5321 limit
        if (strlen($email) > 254) {
            throw new RuntimeException(self::$ERROR_EMAIL_TOO_LONG);
        }
        
        $domain = substr(strrchr($email, '@'), 1);
        if (empty($domain) || (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A'))) {
            throw new RuntimeException(self::$ERROR_EMAIL_INVALID_DOMAIN);
        }
        
        return true;
    }

    // Getters
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function hasPhone(): bool
    {
        return !empty($this->phone);
    }
}