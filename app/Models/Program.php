<?php

namespace App\Models;

use RuntimeException;

class Program
{
    // Constantes de mensajes de error
    public static $ERROR_NAME_EMPTY = 'el nombre del programa no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del programa debe tener al menos 3 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del programa no debe exceder 255 caracteres';
    public static $ERROR_DESCRIPTION_EMPTY = 'la descripción del programa no debe ir vacio';
    public static $ERROR_DESCRIPTION_MIN_LENGTH = 'la descripción del programa debe tener al menos 10 caracteres';
    public static $ERROR_DESCRIPTION_MAX_LENGTH = 'la descripción del programa no debe exceder 2000 caracteres';
    public static $ERROR_BANNER_IMG_EMPTY = 'la imagen banner del programa no debe ir vacio';
    public static $ERROR_BANNER_IMG_INVALID = 'la imagen banner debe ser una URL válida o un path de imagen válido';
    public static $ERROR_START_DATE_EMPTY = 'la fecha de inicio del programa no debe ir vacio';
    public static $ERROR_START_DATE_INVALID_FORMAT = 'la fecha de inicio debe tener formato válido (YYYY-MM-DD)';
    public static $ERROR_START_DATE_TOO_OLD = 'la fecha de inicio no puede ser anterior a 10 años';
    public static $ERROR_END_DATE_EMPTY = 'la fecha de fin del programa no debe ir vacio';
    public static $ERROR_END_DATE_INVALID_FORMAT = 'la fecha de fin debe tener formato válido (YYYY-MM-DD)';
    public static $ERROR_END_DATE_BEFORE_START = 'la fecha de fin debe ser posterior a la fecha de inicio';
    public static $ERROR_DURATION_TOO_LONG = 'la duración del programa no puede exceder 20 años';
    public static $ERROR_URL_INVALID_FORMAT = 'la URL del programa debe tener un formato válido';
    public static $ERROR_URL_INVALID_PROTOCOL = 'la URL del programa debe usar protocolo HTTP o HTTPS';
    public static $ERROR_CONTACT_INVALID = 'el contacto debe ser una instancia de Contact';
    public static $ERROR_PROGRAM_BENEFICIARY_INVALID = 'el beneficiario debe ser una instancia de ProgramBeneficiary';
    public static $ERROR_PROGRAM_STATE_INVALID = 'el estado debe ser una instancia de ProgramState';
    public static $ERROR_COUNTRY_INVALID = 'el país debe ser una instancia de Country';
    public static $ERROR_AGENCY_INVALID = 'la agencia debe ser una instancia de Agency';
    public static $ERROR_SDGS_NOT_ARRAY = 'los SDGs deben ser un array';
    public static $ERROR_SDGS_INVALID_INSTANCE = 'todos los SDGs deben ser instancias de Sdg';
    public static $ERROR_DONORS_NOT_ARRAY = 'los donantes deben ser un array';
    public static $ERROR_DONORS_INVALID_INSTANCE = 'todos los donantes deben ser instancias de Donor';
    public static $ERROR_SDGS_DUPLICATED = 'no se permiten SDGs duplicados en el programa';
    public static $ERROR_DONORS_DUPLICATED = 'no se permiten donantes duplicados en el programa';
    private string $name;
    private string $description;
    private string $bannerImg;
    private string $startDate;
    private string $endDate;
    private string $programUrl;
    private Contact $contact;
    private ProgramBeneficiary $programBeneficiary;
    private ProgramState $programState;
    private Country $country;
    private Agency $agency;
    private array $sdgs;
    private array $programDonors;
    private array $projects;

    public function __construct(
        string $name,
        string $description,
        string $bannerImg,
        string $startDate,
        string $endDate,
        string $programUrl,
        Contact $contact,
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
        $this->contact = $contact;
        $this->programBeneficiary = $programBeneficiary;
        $this->programState = $programState;
        $this->country = $country;
        $this->agency = $agency;
        $this->sdgs = $sdgs;
        $this->programDonors = $programDonors;
        $this->projects = []; // Inicializar como array vacío
    }

    public static function at($name, $description, $bannerImg, $startDate, $endDate, $programUrl, $contact, $programBeneficiary, $programState, $country, $agency, $sdgs, $programDonors): Program
    {
        // Validaciones del nombre
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 3) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }
        if (strlen(trim($name)) > 255) {
            throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        }

        // Validaciones de la descripción
        if (empty(trim($description))) {
            throw new RuntimeException(self::$ERROR_DESCRIPTION_EMPTY);
        }
        if (strlen(trim($description)) < 10) {
            throw new RuntimeException(self::$ERROR_DESCRIPTION_MIN_LENGTH);
        }
        if (strlen(trim($description)) > 2000) {
            throw new RuntimeException(self::$ERROR_DESCRIPTION_MAX_LENGTH);
        }

        // Validaciones del bannerImg
        if (empty(trim($bannerImg))) {
            throw new RuntimeException(self::$ERROR_BANNER_IMG_EMPTY);
        }
        // Validar que sea una URL válida o path válido
        if (!filter_var($bannerImg, FILTER_VALIDATE_URL) && !preg_match('/^[a-zA-Z0-9\/_\-\.]+\.(jpg|jpeg|png|gif|webp)$/i', $bannerImg)) {
            throw new RuntimeException(self::$ERROR_BANNER_IMG_INVALID);
        }

        // Validaciones de startDate
        if (empty(trim($startDate))) {
            throw new RuntimeException(self::$ERROR_START_DATE_EMPTY);
        }
        if (!self::isValidDate($startDate)) {
            throw new RuntimeException(self::$ERROR_START_DATE_INVALID_FORMAT);
        }
        // Validar que no sea una fecha en el pasado muy lejano (más de 10 años)
        $startDateTime = new \DateTime($startDate);
        $tenYearsAgo = new \DateTime('-10 years');
        if ($startDateTime < $tenYearsAgo) {
            throw new RuntimeException(self::$ERROR_START_DATE_TOO_OLD);
        }

        // Validaciones de endDate
        if (empty(trim($endDate))) {
            throw new RuntimeException(self::$ERROR_END_DATE_EMPTY);
        }
        if (!self::isValidDate($endDate)) {
            throw new RuntimeException(self::$ERROR_END_DATE_INVALID_FORMAT);
        }

        // DateTime para comparaciones más precisas
        $startDateTime = new \DateTime($startDate);
        $endDateTime = new \DateTime($endDate);

        if ($endDateTime < $startDateTime) {
            throw new RuntimeException(self::$ERROR_END_DATE_BEFORE_START);
        }
        
        // Validar duración máxima razonable (ej: 20 años)
        $maxDuration = $startDateTime->add(new \DateInterval('P20Y'));
        if ($endDateTime > $maxDuration) {
            throw new RuntimeException(self::$ERROR_DURATION_TOO_LONG);
        }

        // Validaciones de programUrl 
        if (!empty(trim($programUrl))) {
            
            if (!filter_var($programUrl, FILTER_VALIDATE_URL)) {
                throw new RuntimeException(self::$ERROR_URL_INVALID_FORMAT);
            }
            
            // Validar que use HTTPS o HTTP solamente
            $parsedUrl = parse_url($programUrl);
            if (!in_array($parsedUrl['scheme'] ?? '', ['http', 'https'], true)) {
                throw new RuntimeException(self::$ERROR_URL_INVALID_PROTOCOL);
            }
        }

        // Validaciones de contact
        if (!($contact instanceof Contact)) {
            throw new RuntimeException(self::$ERROR_CONTACT_INVALID);
        }

        // Validaciones de programBeneficiary
        if (!($programBeneficiary instanceof ProgramBeneficiary)) {
            throw new RuntimeException(self::$ERROR_PROGRAM_BENEFICIARY_INVALID);
        }

        // Validaciones de programState
        if (!($programState instanceof ProgramState)) {
            throw new RuntimeException(self::$ERROR_PROGRAM_STATE_INVALID);
        }

        // Validaciones de country
        if (!($country instanceof Country)) {
            throw new RuntimeException(self::$ERROR_COUNTRY_INVALID);
        }

        // Validaciones de agency
        if (!($agency instanceof Agency)) {
            throw new RuntimeException(self::$ERROR_AGENCY_INVALID);
        }

        // Validaciones de sdgs 
        if (!is_array($sdgs)) {
            throw new RuntimeException(self::$ERROR_SDGS_NOT_ARRAY);
        }
        
        foreach ($sdgs as $sdg) {
            if (!($sdg instanceof Sdg)) {
                throw new RuntimeException(self::$ERROR_SDGS_INVALID_INSTANCE);
            }
        }

        // Validaciones de programDonors 
        if (!is_array($programDonors)) {
            throw new RuntimeException(self::$ERROR_DONORS_NOT_ARRAY);
        }
        foreach ($programDonors as $donor) {
            if (!($donor instanceof Donor)) {
                throw new RuntimeException(self::$ERROR_DONORS_INVALID_INSTANCE);
            }
        }

        
        if (!empty($sdgs)) {
            $sdgIdentifiers = [];
            foreach ($sdgs as $sdg) {
                $identifier = $sdg->getImage(); 
                if (in_array($identifier, $sdgIdentifiers, true)) {
                    throw new RuntimeException(self::$ERROR_SDGS_DUPLICATED);
                }
                $sdgIdentifiers[] = $identifier;
            }
        }

        
        if (!empty($programDonors)) {
            $donorIdentifiers = [];
            foreach ($programDonors as $donor) {
                $identifier = $donor->getName(); 
                if (in_array($identifier, $donorIdentifiers, true)) {
                    throw new RuntimeException(self::$ERROR_DONORS_DUPLICATED);
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
            $contact,
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

    public function getContact(): Contact
    {
        return $this->contact;
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

    // Métodos de gestión de proyectos
    public function addProject($project): void
    {
        $this->projects[] = $project;
    }

    public function getProjects(): array
    {
        return $this->projects;
    }

    public function getProjectCount(): int
    {
        return count($this->projects);
    }

    public function hasProjects(): bool
    {
        return !empty($this->projects);
    }
}
