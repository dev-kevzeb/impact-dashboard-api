<?php

namespace App\Modules\Program\Domain;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use App\Modules\Contact\Domain\Contact;
use App\Modules\Beneficiary\Domain\Beneficiary;
use App\Modules\ProgramState\Domain\ProgramState;
use App\Modules\Country\Domain\Country;
use App\Modules\Agency\Domain\Agency;
use App\Modules\Sdg\Domain\Sdg;
use App\Modules\Donor\Domain\Donor;

class Program extends Model
{
    protected $table = 'program';  // SINGULAR
    
    protected $fillable = [
        'name',
        'description',
        'banner_img',
        'start_date',
        'end_date',
        'program_url',
        'contact_id',
        'beneficiary_id',
        'program_state_id',
        'country_id',
        'agency_id'
    ];

    // Constantes de mensajes de error
    public static $ERROR_NAME_EMPTY = 'el nombre del programa no debe ir vacío';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del programa debe tener al menos 3 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del programa no debe exceder 255 caracteres';
    public static $ERROR_DESCRIPTION_EMPTY = 'la descripción del programa no debe ir vacío';
    public static $ERROR_DESCRIPTION_MIN_LENGTH = 'la descripción del programa debe tener al menos 10 caracteres';
    public static $ERROR_DESCRIPTION_MAX_LENGTH = 'la descripción del programa no debe exceder 2000 caracteres';
    public static $ERROR_BANNER_IMG_EMPTY = 'la imagen banner del programa no debe ir vacío';
    public static $ERROR_BANNER_IMG_TOO_LONG = 'el nombre de la imagen banner no puede exceder 500 caracteres';
    public static $ERROR_BANNER_IMG_INVALID_CHARACTERS = 'el nombre de la imagen contiene caracteres no permitidos: < > : " | ? * \\ null';
    public static $ERROR_START_DATE_EMPTY = 'la fecha de inicio del programa no debe ir vacío';
    public static $ERROR_START_DATE_INVALID_FORMAT = 'la fecha de inicio debe tener formato válido (YYYY-MM-DD)';
    public static $ERROR_START_DATE_TOO_OLD = 'la fecha de inicio no puede ser anterior a 10 años';
    public static $ERROR_END_DATE_EMPTY = 'la fecha de fin del programa no debe ir vacío';
    public static $ERROR_END_DATE_INVALID_FORMAT = 'la fecha de fin debe tener formato válido (YYYY-MM-DD)';
    public static $ERROR_END_DATE_BEFORE_START = 'la fecha de fin debe ser posterior a la fecha de inicio';
    public static $ERROR_DURATION_TOO_LONG = 'la duración del programa no puede exceder 20 años';
    public static $ERROR_URL_INVALID_FORMAT = 'la URL del programa debe tener un formato válido';
    public static $ERROR_URL_INVALID_PROTOCOL = 'la URL del programa debe usar protocolo HTTP o HTTPS';
    public static $ERROR_CONTACT_INVALID = 'el contacto debe ser una instancia de Contact';
    public static $ERROR_BENEFICIARY_INVALID = 'el beneficiario debe ser una instancia de Beneficiary';
    public static $ERROR_PROGRAM_STATE_INVALID = 'el estado debe ser una instancia de ProgramState';
    public static $ERROR_COUNTRY_INVALID = 'el país debe ser una instancia de Country';
    public static $ERROR_AGENCY_INVALID = 'la agencia debe ser una instancia de Agency';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Factory Method - Crear entidad Program con validaciones
     */
    public static function at(
        string $name,
        string $description,
        string $bannerImg,
        string $startDate,
        string $endDate,
        string $programUrl,
        Contact $contact,
        Beneficiary $beneficiary,
        ProgramState $programState,
        Country $country,
        Agency $agency
    ): Program {
        // Validaciones del nombre
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        
        $trimmedName = trim($name);
        
        if (strlen($trimmedName) < 3) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }
        
        if (strlen($trimmedName) > 255) {
            throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        }

        // Validaciones de la descripción
        if (empty(trim($description))) {
            throw new RuntimeException(self::$ERROR_DESCRIPTION_EMPTY);
        }
        
        $trimmedDescription = trim($description);
        
        if (strlen($trimmedDescription) < 10) {
            throw new RuntimeException(self::$ERROR_DESCRIPTION_MIN_LENGTH);
        }
        
        if (strlen($trimmedDescription) > 2000) {
            throw new RuntimeException(self::$ERROR_DESCRIPTION_MAX_LENGTH);
        }

        // Validaciones del bannerImg
        if (empty(trim($bannerImg))) {
            throw new RuntimeException(self::$ERROR_BANNER_IMG_EMPTY);
        }
        
        $trimmedBanner = trim($bannerImg);
        
        // Validar longitud máxima
        if (strlen($trimmedBanner) > 500) {
            throw new RuntimeException(self::$ERROR_BANNER_IMG_TOO_LONG);
        }
        
        // Validar caracteres peligrosos
        if (!self::hasValidCharacters($trimmedBanner)) {
            throw new RuntimeException(self::$ERROR_BANNER_IMG_INVALID_CHARACTERS);
        }

        // Validaciones de startDate
        if (empty(trim($startDate))) {
            throw new RuntimeException(self::$ERROR_START_DATE_EMPTY);
        }
        
        $trimmedStartDate = trim($startDate);
        
        if (!self::isValidDate($trimmedStartDate)) {
            throw new RuntimeException(self::$ERROR_START_DATE_INVALID_FORMAT);
        }
        
        // Validar que no sea una fecha en el pasado muy lejano (más de 10 años)
        $startDateTime = new \DateTime($trimmedStartDate);
        $tenYearsAgo = new \DateTime('-10 years');
        if ($startDateTime < $tenYearsAgo) {
            throw new RuntimeException(self::$ERROR_START_DATE_TOO_OLD);
        }

        // Validaciones de endDate
        if (empty(trim($endDate))) {
            throw new RuntimeException(self::$ERROR_END_DATE_EMPTY);
        }
        
        $trimmedEndDate = trim($endDate);
        
        if (!self::isValidDate($trimmedEndDate)) {
            throw new RuntimeException(self::$ERROR_END_DATE_INVALID_FORMAT);
        }

        // DateTime para comparaciones
        $endDateTime = new \DateTime($trimmedEndDate);

        if ($endDateTime < $startDateTime) {
            throw new RuntimeException(self::$ERROR_END_DATE_BEFORE_START);
        }
        
        // Validar duración máxima razonable (20 años)
        $maxDuration = clone $startDateTime;
        $maxDuration->add(new \DateInterval('P20Y'));
        if ($endDateTime > $maxDuration) {
            throw new RuntimeException(self::$ERROR_DURATION_TOO_LONG);
        }

        // Validaciones de programUrl (opcional)
        $trimmedUrl = trim($programUrl);
        if (!empty($trimmedUrl)) {
            if (!filter_var($trimmedUrl, FILTER_VALIDATE_URL)) {
                throw new RuntimeException(self::$ERROR_URL_INVALID_FORMAT);
            }
            
            $parsedUrl = parse_url($trimmedUrl);
            if (!in_array($parsedUrl['scheme'] ?? '', ['http', 'https'], true)) {
                throw new RuntimeException(self::$ERROR_URL_INVALID_PROTOCOL);
            }
        }

        // Validaciones de IDs (deben ser positivos)
        if (!($contact instanceof Contact)) {
            throw new RuntimeException(self::$ERROR_CONTACT_INVALID);
        }
        if (!($beneficiary instanceof Beneficiary)) {
            throw new RuntimeException(self::$ERROR_BENEFICIARY_INVALID);
        }
        if (!($programState instanceof ProgramState)) {
            throw new RuntimeException(self::$ERROR_PROGRAM_STATE_INVALID);
        }
        if (!($country instanceof Country)) {
            throw new RuntimeException(self::$ERROR_COUNTRY_INVALID);
        }
        if (!($agency instanceof Agency)) {
            throw new RuntimeException(self::$ERROR_AGENCY_INVALID);
        }

        return new Program([
            'name' => $trimmedName,
            'description' => $trimmedDescription,
            'banner_img' => $trimmedBanner,
            'start_date' => $trimmedStartDate,
            'end_date' => $trimmedEndDate,
            'program_url' => $trimmedUrl,
            'contact_id' => $contact->getKey(),
            'beneficiary_id' => $beneficiary->getKey(),
            'program_state_id' => $programState->getKey(),
            'country_id' => $country->getKey(),
            'agency_id' => $agency->getKey()
        ]);
    }

    /**
     * Validar formato de fecha YYYY-MM-DD
     */
    private static function isValidDate(string $date): bool
    {
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

    /**
     * Validar que el nombre de archivo no contenga caracteres peligrosos
     */
    private static function hasValidCharacters(string $filename): bool
    {
        $dangerousChars = ['<', '>', ':', '"', '|', '?', '*', '\\', "\0"];
        foreach ($dangerousChars as $char) {
            if (str_contains($filename, $char)) {
                return false;
            }
        }
        return true;
    }

    // ============================================
    // GETTERS
    // ============================================

    // ========================================
    // GETTERS (Solo para relaciones - encapsulación de Eloquent)
    // ========================================

    public function getContact(): Contact
    {
        return $this->contact;
    }

    public function getProgramBeneficiary(): Beneficiary
    {
        return $this->beneficiary;
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
        return $this->sdgs ? $this->sdgs->all() : [];
    }

    public function getProgramDonors(): array
    {
        return $this->donors ? $this->donors->all() : [];
    }

    // ============================================
    // RELACIONES ELOQUENT
    // ============================================

    /**
     * Relación 1:1 con Contact
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    /**
     * Relación 1:1 con Beneficiary
     */
    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class, 'beneficiary_id');
    }

    /**
     * Relación 1:1 con ProgramState
     */
    public function programState()
    {
        return $this->belongsTo(ProgramState::class, 'program_state_id');
    }

    /**
     * Relación 1:1 con Country
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * Relación 1:1 con Agency
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    /**
     * Relación M:N con Sdg (a través de tabla pivot program_sdg)
     */
    public function sdgs()
    {
        return $this->belongsToMany(Sdg::class, 'program_sdg', 'program_id', 'sdg_id');
    }

    /**
     * Relación M:N con Donor (a través de tabla pivot program_donor)
     */
    public function donors()
    {
        return $this->belongsToMany(Donor::class, 'program_donor', 'program_id', 'donor_id');
    }

    /**
     * Relación 1:N con Project (un programa tiene muchos proyectos)
     */
    public function projects()
    {
        return $this->hasMany(\App\Modules\Project\Domain\Project::class, 'program_id');
    }
}
