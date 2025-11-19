<?php

namespace App\Modules\Program\Domain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    use HasFactory;
    
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

    // Constantes de mensajes de error (solo reglas de negocio)
    public static $ERROR_NAME_EMPTY = 'el nombre del programa no debe ir vacío';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del programa debe tener al menos 3 caracteres';
    public static $ERROR_DESCRIPTION_EMPTY = 'la descripción del programa no debe ir vacío';
    public static $ERROR_DESCRIPTION_MIN_LENGTH = 'la descripción del programa debe tener al menos 10 caracteres';
    public static $ERROR_DURATION_TOO_LONG = 'la duración del programa no puede exceder 20 años';
    public static $ERROR_END_DATE_BEFORE_START = 'la fecha de fin debe ser posterior a la fecha de inicio';
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
        // Validación de nombre (reglas de negocio)
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        
        $trimmedName = trim($name);
        
        if (strlen($trimmedName) < 3) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }

        // Validación de descripción (reglas de negocio)
        if (empty(trim($description))) {
            throw new RuntimeException(self::$ERROR_DESCRIPTION_EMPTY);
        }
        
        $trimmedDescription = trim($description);
        
        if (strlen($trimmedDescription) < 10) {
            throw new RuntimeException(self::$ERROR_DESCRIPTION_MIN_LENGTH);
        }

        // Validación de fechas (reglas de negocio)
        $startDateTime = new \DateTime(trim($startDate));
        $endDateTime = new \DateTime(trim($endDate));

        if ($endDateTime < $startDateTime) {
            throw new RuntimeException(self::$ERROR_END_DATE_BEFORE_START);
        }
        
        // Validar duración máxima razonable (20 años) - regla de negocio
        $maxDuration = clone $startDateTime;
        $maxDuration->add(new \DateInterval('P20Y'));
        if ($endDateTime > $maxDuration) {
            throw new RuntimeException(self::$ERROR_DURATION_TOO_LONG);
        }

        // Validación de instancias de entidades relacionadas
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
            'banner_img' => trim($bannerImg),
            'start_date' => trim($startDate),
            'end_date' => trim($endDate),
            'program_url' => trim($programUrl),
            'contact_id' => $contact->getKey(),
            'beneficiary_id' => $beneficiary->getKey(),
            'program_state_id' => $programState->getKey(),
            'country_id' => $country->getKey(),
            'agency_id' => $agency->getKey()
        ]);
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
     * TODO: Descomentar cuando el módulo Project esté implementado
     */
    // public function projects()
    // {
    //     return $this->hasMany(\App\Modules\Project\Domain\Project::class, 'program_id');
    // }
    
    /**
     * Laravel Factory integration
     * Requerido para arquitectura modular
     */
    protected static function newFactory()
    {
        return \Database\Factories\ProgramFactory::new();
    }
}
