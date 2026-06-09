<?php

namespace App\Modules\Contact\Domain;

use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Contact extends Model
{
    use HasFactory;
    protected $table = 'contact';
    protected $fillable = ['first_name', 'last_name', 'title', 'email', 'phone'];

    public static $ERROR_FIRST_NAME_EMPTY = 'The contact first name must not be empty';
    public static $ERROR_FIRST_NAME_MIN_LENGTH = 'The contact first name must have at least 2 characters';
    public static $ERROR_FIRST_NAME_MAX_LENGTH = 'The contact first name must not exceed 50 characters';
    public static $ERROR_FIRST_NAME_INVALID_CHARS = 'The contact first name contains invalid characters';

    public static $ERROR_LAST_NAME_EMPTY = 'The contact last name must not be empty';
    public static $ERROR_LAST_NAME_MIN_LENGTH = 'The contact last name must have at least 2 characters';
    public static $ERROR_LAST_NAME_MAX_LENGTH = 'The contact last name must not exceed 50 characters';
    public static $ERROR_LAST_NAME_INVALID_CHARS = 'The contact last name contains invalid characters';

    public static $ERROR_TITLE_EMPTY = 'The contact title must not be empty';
    public static $ERROR_TITLE_MIN_LENGTH = 'The contact title must have at least 2 characters';
    public static $ERROR_TITLE_MAX_LENGTH = 'The contact title must not exceed 100 characters';
    public static $ERROR_TITLE_INVALID_CHARS = 'The contact title contains invalid characters';

    public static $ERROR_EMAIL_EMPTY = 'The contact email must not be empty';
    public static $ERROR_EMAIL_INVALID_FORMAT = 'The contact email must have a valid format';
    public static $ERROR_EMAIL_TOO_LONG = 'The contact email exceeds the maximum allowed length (254 characters)';
    public static $ERROR_EMAIL_INVALID_DOMAIN = 'The email domain is not valid or does not exist';

    public static $ERROR_PHONE_INVALID_FORMAT = 'The phone number format is not valid – use international format';
    public static $ERROR_PHONE_TOO_SHORT = 'The phone number must have at least 7 digits';
    public static $ERROR_PHONE_TOO_LONG = 'The phone number must not exceed 15 digits';

    
    public static function newFactory()
    {
        return ContactFactory::new();
    }

    public static function at(string $firstName, string $lastName, string $title, string $email, string $phone = ""): Contact
    {
        if (empty(trim($firstName))) throw new RuntimeException(self::$ERROR_FIRST_NAME_EMPTY);
        
        $trimmedFirstName = trim($firstName);
        
        if (!self::hasValidTextCharacters($trimmedFirstName)) throw new RuntimeException(self::$ERROR_FIRST_NAME_INVALID_CHARS);
        if (strlen($trimmedFirstName) < 2) throw new RuntimeException(self::$ERROR_FIRST_NAME_MIN_LENGTH);
        if (strlen($trimmedFirstName) > 50) throw new RuntimeException(self::$ERROR_FIRST_NAME_MAX_LENGTH);
        if (empty(trim($lastName))) throw new RuntimeException(self::$ERROR_LAST_NAME_EMPTY);
        
        $trimmedLastName = trim($lastName);
    
        if (!self::hasValidTextCharacters($trimmedLastName)) throw new RuntimeException(self::$ERROR_LAST_NAME_INVALID_CHARS);
        if (strlen($trimmedLastName) < 2) throw new RuntimeException(self::$ERROR_LAST_NAME_MIN_LENGTH);
        if (strlen($trimmedLastName) > 50) throw new RuntimeException(self::$ERROR_LAST_NAME_MAX_LENGTH);
        if (empty(trim($title))) throw new RuntimeException(self::$ERROR_TITLE_EMPTY);
    
        $trimmedTitle = trim($title);
        
        if (!self::hasValidTextCharacters($trimmedTitle)) throw new RuntimeException(self::$ERROR_TITLE_INVALID_CHARS);
        if (strlen($trimmedTitle) < 2) throw new RuntimeException(self::$ERROR_TITLE_MIN_LENGTH);
        if (strlen($trimmedTitle) > 100) throw new RuntimeException(self::$ERROR_TITLE_MAX_LENGTH);
        if (empty(trim($email))) throw new RuntimeException(self::$ERROR_EMAIL_EMPTY);
        $trimmedEmail = trim($email);
        
        if (!filter_var(strtolower($trimmedEmail), FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE)) throw new RuntimeException(self::$ERROR_EMAIL_INVALID_FORMAT);
        if (strlen($trimmedEmail) > 254) throw new RuntimeException(self::$ERROR_EMAIL_TOO_LONG);

        $domain = substr(strrchr($trimmedEmail, '@'), 1);
        if (empty($domain) || (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A'))) throw new RuntimeException(self::$ERROR_EMAIL_INVALID_DOMAIN);
        $trimmedPhone = trim($phone);
        if (!empty($trimmedPhone)) {
            if (!preg_match('/^(\+?\d{1,4})?[\s\-]?\(?\d{1,4}\)?[\s\-]?\d+(\s?\-?\d+)*$/', $trimmedPhone)) throw new RuntimeException(self::$ERROR_PHONE_INVALID_FORMAT);
            $digitsOnly = preg_replace('/\D/', '', $trimmedPhone);
            $length = strlen($digitsOnly);
            
            if ($length < 7) throw new RuntimeException(self::$ERROR_PHONE_TOO_SHORT);
            if ($length > 15) throw new RuntimeException(self::$ERROR_PHONE_TOO_LONG);
            
        }

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
        return preg_match('/^[a-zA-ZÀ-ÿñÑ\s\'\-\.\,\/\(\)]+$/u', $text);
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

    // ============================================
    // RELACIONES ELOQUENT
    // ============================================

    /**
     * Relación 1:N con Program
     * Un contacto puede estar asociado a múltiples programas
     */
    public function programs()
    {
        return $this->hasMany(\App\Modules\Program\Domain\Program::class, 'contact_id');
    }
}