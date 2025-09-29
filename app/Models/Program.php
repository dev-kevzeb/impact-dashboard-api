<?php

namespace App\Models;

use RuntimeException;

class Program
{
    // Constantes de mensajes de error
    public const ERROR_NAME_EMPTY = 'el nombre del programa no debe ir vacio';
    public const ERROR_NAME_MIN_LENGTH = 'el nombre del programa debe tener al menos 3 caracteres';
    public const ERROR_NAME_MAX_LENGTH = 'el nombre del programa no debe exceder 255 caracteres';
    public const ERROR_DESCRIPTION_EMPTY = 'la descripción del programa no debe ir vacio';
    public const ERROR_DESCRIPTION_MIN_LENGTH = 'la descripción del programa debe tener al menos 10 caracteres';
    public const ERROR_DESCRIPTION_MAX_LENGTH = 'la descripción del programa no debe exceder 2000 caracteres';
    public const ERROR_BANNER_IMG_EMPTY = 'la imagen banner del programa no debe ir vacio';
    public const ERROR_BANNER_IMG_INVALID = 'la imagen banner debe ser una URL válida o un path de imagen válido';
    public const ERROR_START_DATE_EMPTY = 'la fecha de inicio del programa no debe ir vacio';
    public const ERROR_START_DATE_INVALID_FORMAT = 'la fecha de inicio debe tener formato válido (YYYY-MM-DD)';
    public const ERROR_START_DATE_TOO_OLD = 'la fecha de inicio no puede ser anterior a 10 años';
    public const ERROR_END_DATE_EMPTY = 'la fecha de fin del programa no debe ir vacio';
    public const ERROR_END_DATE_INVALID_FORMAT = 'la fecha de fin debe tener formato válido (YYYY-MM-DD)';
    public const ERROR_END_DATE_BEFORE_START = 'la fecha de fin debe ser posterior a la fecha de inicio';
    public const ERROR_DURATION_TOO_LONG = 'la duración del programa no puede exceder 20 años';
    public const ERROR_URL_INVALID_FORMAT = 'la URL del programa debe tener un formato válido';
    public const ERROR_URL_INVALID_PROTOCOL = 'la URL del programa debe usar protocolo HTTP o HTTPS';
    public const ERROR_CONTACT_FIRST_NAME_EMPTY = 'el nombre del contacto no debe ir vacio';
    public const ERROR_CONTACT_FIRST_NAME_MIN_LENGTH = 'el nombre del contacto debe tener al menos 2 caracteres';
    public const ERROR_CONTACT_FIRST_NAME_MAX_LENGTH = 'el nombre del contacto no debe exceder 50 caracteres';
    public const ERROR_CONTACT_FIRST_NAME_INVALID_CHARS = 'el nombre del contacto contiene caracteres no válidos';
    public const ERROR_CONTACT_LAST_NAME_EMPTY = 'el apellido del contacto no debe ir vacio';
    public const ERROR_CONTACT_LAST_NAME_MIN_LENGTH = 'el apellido del contacto debe tener al menos 2 caracteres';
    public const ERROR_CONTACT_LAST_NAME_MAX_LENGTH = 'el apellido del contacto no debe exceder 50 caracteres';
    public const ERROR_CONTACT_LAST_NAME_INVALID_CHARS = 'el apellido del contacto contiene caracteres no válidos';
    public const ERROR_CONTACT_TITLE_EMPTY = 'el título del contacto no debe ir vacio';
    public const ERROR_CONTACT_TITLE_MIN_LENGTH = 'el título del contacto debe tener al menos 2 caracteres';
    public const ERROR_CONTACT_TITLE_MAX_LENGTH = 'el título del contacto no debe exceder 100 caracteres';
    public const ERROR_CONTACT_TITLE_INVALID_CHARS = 'el título del contacto contiene caracteres no válidos';
    public const ERROR_CONTACT_EMAIL_EMPTY = 'el email del contacto no debe ir vacio';
    public const ERROR_CONTACT_EMAIL_INVALID_FORMAT = 'el email del contacto debe tener un formato válido';
    public const ERROR_CONTACT_EMAIL_TOO_LONG = 'el email del contacto excede la longitud máxima permitida';
    public const ERROR_CONTACT_EMAIL_INVALID_DOMAIN = 'el dominio del email no es válido o no existe';
    public const ERROR_CONTACT_PHONE_INVALID_FORMAT = 'el formato del teléfono no es válido - use formato internacional';
    public const ERROR_CONTACT_PHONE_TOO_SHORT = 'el teléfono debe tener al menos 7 dígitos';
    public const ERROR_CONTACT_PHONE_TOO_LONG = 'el teléfono no debe exceder 15 dígitos';
    public const ERROR_PROGRAM_BENEFICIARY_INVALID = 'el beneficiario debe ser una instancia de ProgramBeneficiary';
    public const ERROR_PROGRAM_STATE_INVALID = 'el estado debe ser una instancia de ProgramState';
    public const ERROR_COUNTRY_INVALID = 'el país debe ser una instancia de Country';
    public const ERROR_AGENCY_INVALID = 'la agencia debe ser una instancia de Agency';
    public const ERROR_SDGS_NOT_ARRAY = 'los SDGs deben ser un array';
    public const ERROR_SDGS_INVALID_INSTANCE = 'todos los SDGs deben ser instancias de Sdg';
    public const ERROR_DONORS_NOT_ARRAY = 'los donantes deben ser un array';
    public const ERROR_DONORS_INVALID_INSTANCE = 'todos los donantes deben ser instancias de Donor';
    public const ERROR_SDGS_DUPLICATED = 'no se permiten SDGs duplicados en el programa';
    public const ERROR_DONORS_DUPLICATED = 'no se permiten donantes duplicados en el programa';
    private string $name;
    private string $description;
    private string $bannerImg;
    private string $startDate;
    private string $endDate;
    private string $programUrl;
    private string $contactFirstName;
    private string $contactLastName;
    private string $contactTitle;
    private string $contactEmail;
    private string $contactPhone;
    private ProgramBeneficiary $programBeneficiary;
    private ProgramState $programState;
    private Country $country;
    private Agency $agency;
    private array $sdgs;
    private array $programDonors;

    public function __construct(
        string $name,
        string $description,
        string $bannerImg,
        string $startDate,
        string $endDate,
        string $programUrl,
        string $contactFirstName,
        string $contactLastName,
        string $contactTitle,
        string $contactEmail,
        string $contactPhone,
        ProgramBeneficiary $programBeneficiary,
        ProgramState $programState,
        Country $country,
        Agency $agency,
        array $sdgs,
        array $programDonors
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->bannerImg = $bannerImg;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->programUrl = $programUrl;
        $this->contactFirstName = $contactFirstName;
        $this->contactLastName = $contactLastName;
        $this->contactTitle = $contactTitle;
        $this->contactEmail = $contactEmail;
        $this->contactPhone = $contactPhone;
        $this->programBeneficiary = $programBeneficiary;
        $this->programState = $programState;
        $this->country = $country;
        $this->agency = $agency;
        $this->sdgs = $sdgs;
        $this->programDonors = $programDonors;
    }

    public static function at($name, $description, $bannerImg, $startDate, $endDate, $programUrl, $contactFirstName, $contactLastName, $contactTitle, $contactEmail, $contactPhone, $programBeneficiary, $programState, $country, $agency, $sdgs, $programDonors): Program
    {
        // Validaciones del nombre
        if (empty(trim($name))) {
            throw new RuntimeException(self::ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 3) {
            throw new RuntimeException(self::ERROR_NAME_MIN_LENGTH);
        }
        if (strlen(trim($name)) > 255) {
            throw new RuntimeException(self::ERROR_NAME_MAX_LENGTH);
        }

        // Validaciones de la descripción
        if (empty(trim($description))) {
            throw new RuntimeException(self::ERROR_DESCRIPTION_EMPTY);
        }
        if (strlen(trim($description)) < 10) {
            throw new RuntimeException(self::ERROR_DESCRIPTION_MIN_LENGTH);
        }
        if (strlen(trim($description)) > 2000) {
            throw new RuntimeException(self::ERROR_DESCRIPTION_MAX_LENGTH);
        }

        // Validaciones del bannerImg
        if (empty(trim($bannerImg))) {
            throw new RuntimeException(self::ERROR_BANNER_IMG_EMPTY);
        }
        // Validar que sea una URL válida o path válido
        if (!filter_var($bannerImg, FILTER_VALIDATE_URL) && !preg_match('/^[a-zA-Z0-9\/_\-\.]+\.(jpg|jpeg|png|gif|webp)$/i', $bannerImg)) {
            throw new RuntimeException(self::ERROR_BANNER_IMG_INVALID);
        }

        // Validaciones de startDate
        if (empty(trim($startDate))) {
            throw new RuntimeException(self::ERROR_START_DATE_EMPTY);
        }
        if (!self::isValidDate($startDate)) {
            throw new RuntimeException(self::ERROR_START_DATE_INVALID_FORMAT);
        }
        // Validar que no sea una fecha en el pasado muy lejano (más de 10 años)
        $startDateTime = new \DateTime($startDate);
        $tenYearsAgo = new \DateTime('-10 years');
        if ($startDateTime < $tenYearsAgo) {
            throw new RuntimeException(self::ERROR_START_DATE_TOO_OLD);
        }

        // Validaciones de endDate
        if (empty(trim($endDate))) {
            throw new RuntimeException(self::ERROR_END_DATE_EMPTY);
        }
        if (!self::isValidDate($endDate)) {
            throw new RuntimeException(self::ERROR_END_DATE_INVALID_FORMAT);
        }

        // DateTime para comparaciones más precisas
        $startDateTime = new \DateTime($startDate);
        $endDateTime = new \DateTime($endDate);

        if ($endDateTime < $startDateTime) {
            throw new RuntimeException(self::ERROR_END_DATE_BEFORE_START);
        }
        
        // Validar duración máxima razonable (ej: 20 años)
        $maxDuration = $startDateTime->add(new \DateInterval('P20Y'));
        if ($endDateTime > $maxDuration) {
            throw new RuntimeException(self::ERROR_DURATION_TOO_LONG);
        }

        // Validaciones de programUrl 
        if (!empty(trim($programUrl))) {
            
            if (!filter_var($programUrl, FILTER_VALIDATE_URL)) {
                throw new RuntimeException(self::ERROR_URL_INVALID_FORMAT);
            }
            
            // Validar que use HTTPS o HTTP solamente
            $parsedUrl = parse_url($programUrl);
            if (!in_array($parsedUrl['scheme'] ?? '', ['http', 'https'], true)) {
                throw new RuntimeException(self::ERROR_URL_INVALID_PROTOCOL);
            }
        }

        // Validaciones de contactFirstName
        if (empty(trim($contactFirstName))) {
            throw new RuntimeException(self::ERROR_CONTACT_FIRST_NAME_EMPTY);
        }
        if (strlen(trim($contactFirstName)) < 2) {
            throw new RuntimeException(self::ERROR_CONTACT_FIRST_NAME_MIN_LENGTH);
        }
        if (strlen(trim($contactFirstName)) > 50) {
            throw new RuntimeException(self::ERROR_CONTACT_FIRST_NAME_MAX_LENGTH);
        }
        // Validar que contenga solo letras, espacios y caracteres válidos para nombres
        if (!preg_match('/^[a-zA-ZÀ-ÿñÑ\s\-\'\.]+$/u', trim($contactFirstName))) {
            throw new RuntimeException(self::ERROR_CONTACT_FIRST_NAME_INVALID_CHARS);
        }

        // Validaciones de contactLastName
        if (empty(trim($contactLastName))) {
            throw new RuntimeException(self::ERROR_CONTACT_LAST_NAME_EMPTY);
        }
        if (strlen(trim($contactLastName)) < 2) {
            throw new RuntimeException(self::ERROR_CONTACT_LAST_NAME_MIN_LENGTH);
        }
        if (strlen(trim($contactLastName)) > 50) {
            throw new RuntimeException(self::ERROR_CONTACT_LAST_NAME_MAX_LENGTH);
        }
        // Validar que contenga solo letras, espacios y caracteres válidos para apellidos
        if (!preg_match('/^[a-zA-ZÀ-ÿñÑ\s\-\'\.]+$/u', trim($contactLastName))) {
            throw new RuntimeException(self::ERROR_CONTACT_LAST_NAME_INVALID_CHARS);
        }

        // Validaciones de contactTitle
        if (empty(trim($contactTitle))) {
            throw new RuntimeException(self::ERROR_CONTACT_TITLE_EMPTY);
        }
        if (strlen(trim($contactTitle)) < 2) {
            throw new RuntimeException(self::ERROR_CONTACT_TITLE_MIN_LENGTH);
        }
        if (strlen(trim($contactTitle)) > 100) {
            throw new RuntimeException(self::ERROR_CONTACT_TITLE_MAX_LENGTH);
        }
        // Validar formato de título profesional
        if (!preg_match('/^[a-zA-ZÀ-ÿñÑ\s\-\.\,\/]+$/u', trim($contactTitle))) {
            throw new RuntimeException(self::ERROR_CONTACT_TITLE_INVALID_CHARS);
        }

        // Validaciones de contactEmail
        if (empty(trim($contactEmail))) {
            throw new RuntimeException(self::ERROR_CONTACT_EMAIL_EMPTY);
        }

        // validacion robusta con RFC
        $email = trim(strtolower($contactEmail));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE)) {
            throw new RuntimeException(self::ERROR_CONTACT_EMAIL_INVALID_FORMAT);
        }
        
        if (strlen($email) > 254) { // RFC 5321 limit
            throw new RuntimeException(self::ERROR_CONTACT_EMAIL_TOO_LONG);
        }
        
        // Validar dominio 
        $domain = substr(strrchr($email, '@'), 1);
        if (empty($domain) || !checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
            throw new RuntimeException(self::ERROR_CONTACT_EMAIL_INVALID_DOMAIN);
        }

        // Validaciones de contactPhone 
        if (!empty(trim($contactPhone))) {
            // Solo validar formato si no está vacío
            $phoneString = trim($contactPhone);

            // Acepta formatos: +1234567890, +12 345 678 9012, +1-234-567-8901
            if (!preg_match('/^(\+?\d{1,4})?[\s\-]?\(?\d{1,4}\)?[\s\-]?\d{6,14}$/', $phoneString)) {
                throw new RuntimeException(self::ERROR_CONTACT_PHONE_INVALID_FORMAT);
            }
            
            // Validar longitud total 
            $digitsOnly = preg_replace('/\D/', '', $phoneString);
            if (strlen($digitsOnly) < 7) {
                throw new RuntimeException(self::ERROR_CONTACT_PHONE_TOO_SHORT);
            }
            if (strlen($digitsOnly) > 15) {
                throw new RuntimeException(self::ERROR_CONTACT_PHONE_TOO_LONG);
            }
        }

        // Validaciones de programBeneficiary
        if (!($programBeneficiary instanceof ProgramBeneficiary)) {
            throw new RuntimeException(self::ERROR_PROGRAM_BENEFICIARY_INVALID);
        }

        // Validaciones de programState
        if (!($programState instanceof ProgramState)) {
            throw new RuntimeException(self::ERROR_PROGRAM_STATE_INVALID);
        }

        // Validaciones de country
        if (!($country instanceof Country)) {
            throw new RuntimeException(self::ERROR_COUNTRY_INVALID);
        }

        // Validaciones de agency
        if (!($agency instanceof Agency)) {
            throw new RuntimeException(self::ERROR_AGENCY_INVALID);
        }

        // Validaciones de sdgs 
        if (!is_array($sdgs)) {
            throw new RuntimeException(self::ERROR_SDGS_NOT_ARRAY);
        }
        
        foreach ($sdgs as $sdg) {
            if (!($sdg instanceof Sdg)) {
                throw new RuntimeException(self::ERROR_SDGS_INVALID_INSTANCE);
            }
        }

        // Validaciones de programDonors 
        if (!is_array($programDonors)) {
            throw new RuntimeException(self::ERROR_DONORS_NOT_ARRAY);
        }
        foreach ($programDonors as $donor) {
            if (!($donor instanceof Donor)) {
                throw new RuntimeException(self::ERROR_DONORS_INVALID_INSTANCE);
            }
        }

        
        if (!empty($sdgs)) {
            $sdgIdentifiers = [];
            foreach ($sdgs as $sdg) {
                $identifier = $sdg->getImage(); 
                if (in_array($identifier, $sdgIdentifiers, true)) {
                    throw new RuntimeException(self::ERROR_SDGS_DUPLICATED);
                }
                $sdgIdentifiers[] = $identifier;
            }
        }

        
        if (!empty($programDonors)) {
            $donorIdentifiers = [];
            foreach ($programDonors as $donor) {
                $identifier = $donor->getName(); 
                if (in_array($identifier, $donorIdentifiers, true)) {
                    throw new RuntimeException(self::ERROR_DONORS_DUPLICATED);
                }
                $donorIdentifiers[] = $identifier;
            }
        }

        return new Program(
            $name,
            $description,
            $bannerImg,
            $startDate,
            $endDate,
            $programUrl,
            $contactFirstName,
            $contactLastName,
            $contactTitle,
            $contactEmail,
            $contactPhone,
            $programBeneficiary,
            $programState,
            $country,
            $agency,
            $sdgs,
            $programDonors
        );
    }

    
    private static function isValidDate(string $date): bool
    {
        $date = trim($date);
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        
        $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
        $errors = \DateTime::getLastErrors();
        
        if ($errors && ($errors['error_count'] > 0 || $errors['warning_count'] > 0)) {
            return false;
        }
        
        return $dateTime && $dateTime->format('Y-m-d') === $date;
    }

    // Getters
    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getBannerImg(): string
    {
        return $this->bannerImg;
    }

    public function getStartDate(): string
    {
        return $this->startDate;
    }

    public function getEndDate(): string
    {
        return $this->endDate;
    }

    public function getProgramUrl(): string
    {
        return $this->programUrl;
    }

    public function getContactFirstName(): string
    {
        return $this->contactFirstName;
    }

    public function getContactLastName(): string
    {
        return $this->contactLastName;
    }

    public function getContactTitle(): string
    {
        return $this->contactTitle;
    }

    public function getContactEmail(): string
    {
        return $this->contactEmail;
    }

    public function getContactPhone(): string
    {
        return $this->contactPhone;
    }

    public function getProgramBeneficiary(): ProgramBeneficiary
    {
        return $this->programBeneficiary;
    }

    public function getProgramState(): ProgramState
    {
        return $this->programState;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function getAgency(): Agency
    {
        return $this->agency;
    }

    public function getSdgs(): array
    {
        return $this->sdgs;
    }

    public function getProgramDonors(): array
    {
        return $this->programDonors;
    }
}
