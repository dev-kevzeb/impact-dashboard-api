<?php

namespace App\Modules\Contact\Domain;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Contact extends Model
{
    // tabla asociada para el orm eloquent
    protected $table = 'contact';
    // atributos asignables
    protected $fillable = ['first_name', 'last_name', 'title', 'email', 'phone'];
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
        
        // Validar formato básico
        if (!filter_var(strtolower($trimmedEmail), FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE)) {
            throw new RuntimeException(self::$ERROR_EMAIL_INVALID_FORMAT);
        }
        
        // Validar longitud máxima RFC 5321
        if (strlen($trimmedEmail) > 254) {
            throw new RuntimeException(self::$ERROR_EMAIL_TOO_LONG);
        }
        
        // Validar dominio DNS
        $domain = substr(strrchr($trimmedEmail, '@'), 1);
        if (empty($domain) || (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A'))) {
            throw new RuntimeException(self::$ERROR_EMAIL_INVALID_DOMAIN);
        }

        // Validar phone (opcional)
        $trimmedPhone = trim($phone);
        if (!empty($trimmedPhone)) {
            // Validar formato básico
            if (!preg_match('/^(\+?\d{1,4})?[\s\-]?\(?\d{1,4}\)?[\s\-]?\d+(\s?\-?\d+)*$/', $trimmedPhone)) {
                throw new RuntimeException(self::$ERROR_PHONE_INVALID_FORMAT);
            }
            
            // Validar longitud de dígitos
            $digitsOnly = preg_replace('/\D/', '', $trimmedPhone);
            $length = strlen($digitsOnly);
            
            if ($length < 7) {
                throw new RuntimeException(self::$ERROR_PHONE_TOO_SHORT);
            }
            
            if ($length > 15) {
                throw new RuntimeException(self::$ERROR_PHONE_TOO_LONG);
            }
        }
        // capitalize php
        $first = mb_convert_case(mb_strtolower($trimmedFirstName, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        $last  = mb_convert_case(mb_strtolower($trimmedLastName, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        $title = mb_convert_case(mb_strtolower($trimmedTitle, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
       return new self([
            'first_name' => $first,
            'last_name'  => $last,
            'title'      => $title,
            'email'      => strtolower($trimmedEmail),
            'phone'      => $trimmedPhone,
        ]);
    }

    private static function hasValidTextCharacters(string $text): bool
    {
        // Letras, espacios, acentos, guiones, apostrofes, ñÑ y puntuación profesional
        return preg_match('/^[a-zA-ZÀ-ÿñÑ\s\'-\.,\/]+$/u', $text);
    }

    // Getters
    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function getLastName(): string
    {
        return $this->last_name;
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
        return $this->first_name . ' ' . $this->last_name;
    }

    public function hasPhone(): bool
    {
        return !empty($this->phone);
    }
}