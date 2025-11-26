<?php

namespace App\Models;

use App\Modules\Agency\Domain\Agency;
use App\Modules\Beneficiary\Domain\Beneficiary;
use App\Modules\Contact\Domain\Contact;
use App\Modules\Country\Domain\Country;
use App\Modules\Donor\Domain\Donor;
use App\Modules\Indicator\Domain\Indicator;
use App\Modules\ProjectState\Domain\ProjectState;
use RuntimeException;

class Project
{
    private string $name;
    private string $description;
    private string $projectUrl;
    private string $startDate;
    private string $endDate;
    private float $progress;
    private string $comments;
    private float $projectBudget;
    private bool $shared;
    private Contact $contact;
    private Beneficiary $projectBeneficiary;
    private ProjectState $projectState;
    private Country $country;
    private Agency $agency;
    private Indicator $indicator;
    private array $projectDonors;
    
    // Constantes de mensajes de error
    public static $ERROR_NAME_EMPTY = 'el nombre del proyecto no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del proyecto debe tener al menos 3 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del proyecto no debe exceder 255 caracteres';
    public static $ERROR_DESCRIPTION_EMPTY = 'la descripción del proyecto no debe ir vacio';
    public static $ERROR_DESCRIPTION_MIN_LENGTH = 'la descripción del proyecto debe tener al menos 10 caracteres';
    public static $ERROR_DESCRIPTION_MAX_LENGTH = 'la descripción del proyecto no debe exceder 2000 caracteres';
    public static $ERROR_PROJECT_URL_INVALID_FORMAT = 'la URL del proyecto debe tener un formato válido';
    public static $ERROR_PROJECT_URL_INVALID_PROTOCOL = 'la URL del proyecto debe usar protocolo HTTP o HTTPS';
    public static $ERROR_START_DATE_EMPTY = 'la fecha de inicio del proyecto no debe ir vacio';
    public static $ERROR_START_DATE_INVALID_FORMAT = 'la fecha de inicio debe tener formato válido (YYYY-MM-DD)';
    public static $ERROR_END_DATE_EMPTY = 'la fecha de fin del proyecto no debe ir vacio';
    public static $ERROR_END_DATE_INVALID_FORMAT = 'la fecha de fin debe tener formato válido (YYYY-MM-DD)';
    public static $ERROR_END_DATE_BEFORE_START = 'la fecha de fin debe ser posterior a la fecha de inicio';
    public static $ERROR_PROGRESS_INVALID = 'el progreso debe ser un número entre 0 y 100';
    public static $ERROR_COMMENTS_MAX_LENGTH = 'los comentarios no deben exceder 1000 caracteres';
    public static $ERROR_PROJECT_BUDGET_INVALID = 'el presupuesto del proyecto debe ser un número mayor que 0';
    public static $ERROR_INDICATOR_INVALID = 'el indicador debe ser una instancia de Indicator';
    public static $ERROR_COUNTRY_INVALID = 'el país debe ser una instancia de Country';
    public static $ERROR_AGENCY_INVALID = 'la agencia debe ser una instancia de Agency';
    public static $ERROR_PROJECT_STATE_INVALID = 'el estado debe ser una instancia de ProjectState';
    public static $ERROR_PROJECT_BENEFICIARY_INVALID = 'el beneficiario debe ser una instancia de Beneficiary';
    public static $ERROR_CONTACT_INVALID = 'el contacto debe ser una instancia de Contact';
    public static $ERROR_SHARED_INVALID = 'el campo compartido debe ser booleano';
    public static $ERROR_PROJECT_DONORS_NOT_ARRAY = 'los donantes deben ser un array';
    public static $ERROR_PROJECT_DONORS_INVALID_INSTANCE = 'todos los donantes deben ser instancias de ProjectDonor';
    public static $ERROR_PROJECT_DONORS_DUPLICATED = 'no se permiten donantes duplicados en el proyecto';

    public function __construct(
        string $name,
        string $description,
        string $projectUrl,
        string $startDate,
        string $endDate,
        float $progress,
        string $comments,
        float $projectBudget,
        bool $shared,
        Contact $contact,
        Beneficiary $projectBeneficiary,
        ProjectState $projectState,
        Country $country,
        Agency $agency,
        Indicator $indicator,
        array $projectDonors
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->projectUrl = $projectUrl;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->progress = $progress;
        $this->comments = $comments;
        $this->projectBudget = $projectBudget;
        $this->shared = $shared;
        $this->contact = $contact;
        $this->projectBeneficiary = $projectBeneficiary;
        $this->projectState = $projectState;
        $this->country = $country;
        $this->agency = $agency;
        $this->indicator = $indicator;
        $this->projectDonors = $projectDonors;
    }

    public static function at(
        $name,
        $description,
        $projectUrl,
        $startDate,
        $endDate,
        $progress,
        $comments,
        $projectBudget,
        $shared,
        $contact,
        $projectBeneficiary,
        $projectState,
        $country,
        $agency,
        $indicator,
        $projectDonors
    ): Project {
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

        // Validaciones de projectUrl 
        if (!empty(trim($projectUrl))) {
            if (!filter_var($projectUrl, FILTER_VALIDATE_URL)) {
                throw new RuntimeException(self::$ERROR_PROJECT_URL_INVALID_FORMAT);
            }
            
            // Validar que use HTTPS o HTTP solamente
            $parsedUrl = parse_url($projectUrl);
            if (!in_array($parsedUrl['scheme'] ?? '', ['http', 'https'], true)) {
                throw new RuntimeException(self::$ERROR_PROJECT_URL_INVALID_PROTOCOL);
            }
        }

        // Validaciones de startDate
        if (empty(trim($startDate))) {
            throw new RuntimeException(self::$ERROR_START_DATE_EMPTY);
        }
        if (!self::isValidDate($startDate)) {
            throw new RuntimeException(self::$ERROR_START_DATE_INVALID_FORMAT);
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

        // Validaciones de progress
        if (!is_numeric($progress)) {
            throw new RuntimeException(self::$ERROR_PROGRESS_INVALID);
        }
        $progressFloat = (float) $progress;
        if ($progressFloat < 0 || $progressFloat > 100) {
            throw new RuntimeException(self::$ERROR_PROGRESS_INVALID);
        }

        // Validaciones de comments
        if (strlen(trim($comments)) > 1000) {
            throw new RuntimeException(self::$ERROR_COMMENTS_MAX_LENGTH);
        }

        // Validaciones de projectBudget
        if (!is_numeric($projectBudget)) {
            throw new RuntimeException(self::$ERROR_PROJECT_BUDGET_INVALID);
        }
        $projectBudgetFloat = (float) $projectBudget;
        if ($projectBudgetFloat <= 0) {
            throw new RuntimeException(self::$ERROR_PROJECT_BUDGET_INVALID);
        }

        // Validaciones de shared
        if (!is_bool($shared)) {
            throw new RuntimeException(self::$ERROR_SHARED_INVALID);
        }

        // Validaciones de entidades
        if (!($contact instanceof Contact)) {
            throw new RuntimeException(self::$ERROR_CONTACT_INVALID);
        }

        if (!($projectBeneficiary instanceof Beneficiary)) {
            throw new RuntimeException(self::$ERROR_PROJECT_BENEFICIARY_INVALID);
        }

        if (!($projectState instanceof ProjectState)) {
            throw new RuntimeException(self::$ERROR_PROJECT_STATE_INVALID);
        }

        if (!($country instanceof Country)) {
            throw new RuntimeException(self::$ERROR_COUNTRY_INVALID);
        }

        if (!($agency instanceof Agency)) {
            throw new RuntimeException(self::$ERROR_AGENCY_INVALID);
        }

        if (!($indicator instanceof Indicator)) {
            throw new RuntimeException(self::$ERROR_INDICATOR_INVALID);
        }

        // Validaciones de projectDonors 
        if (!is_array($projectDonors)) {
            throw new RuntimeException(self::$ERROR_PROJECT_DONORS_NOT_ARRAY);
        }
        foreach ($projectDonors as $donor) {
            if (!($donor instanceof Donor)) {
                throw new RuntimeException(self::$ERROR_PROJECT_DONORS_INVALID_INSTANCE);
            }
        }

        // Validar duplicados de ProjectDonors
        if (!empty($projectDonors)) {
            $donorIdentifiers = [];
            foreach ($projectDonors as $projectDonor) {
                $identifier = $projectDonor->getDonorName(); 
                if (in_array($identifier, $donorIdentifiers, true)) {
                    throw new RuntimeException(self::$ERROR_PROJECT_DONORS_DUPLICATED);
                }
                $donorIdentifiers[] = $identifier;
            }
        }

        return new Project(
            trim($name),
            trim($description),
            trim($projectUrl),
            $startDate,
            $endDate,
            $progressFloat,
            trim($comments),
            $projectBudgetFloat,
            $shared,
            $contact,
            $projectBeneficiary,
            $projectState,
            $country,
            $agency,
            $indicator,
            $projectDonors
        );
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

    public function getProjectUrl(): string
    {
        return $this->projectUrl;
    }

    public function getStartDate(): string
    {
        return $this->startDate;
    }

    public function getEndDate(): string
    {
        return $this->endDate;
    }

    public function getProgress(): float
    {
        return $this->progress;
    }

    public function getComments(): string
    {
        return $this->comments;
    }

    public function getProjectBudget(): float
    {
        return $this->projectBudget;
    }

    public function isShared(): bool
    {
        return $this->shared;
    }

    public function getContact(): Contact
    {
        return $this->contact;
    }

    public function getProjectBeneficiary(): Beneficiary
    {
        return $this->projectBeneficiary;
    }

    public function getProjectState(): ProjectState
    {
        return $this->projectState;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function getAgency(): Agency
    {
        return $this->agency;
    }

    public function getIndicator(): Indicator
    {
        return $this->indicator;
    }

    public function getProjectDonors(): array
    {
        return $this->projectDonors;
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
}