<?php

namespace App\Modules\Program\Domain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use App\Modules\Contact\Domain\Contact;
use App\Modules\ProgramState\Domain\ProgramState;
use App\Modules\Sdg\Domain\Sdg;

class Program extends Model
{
    use HasFactory;
    
    protected $table = 'program';  // SINGULAR
    
    protected $fillable = [
        'name',
        'description',
        'banner_img',
        'program_url',
        'contact_id',
        'program_state_id'
    ];

    // Constantes de mensajes de error (solo reglas de negocio)
    public static $ERROR_NAME_EMPTY = 'el nombre del programa no debe ir vacío';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del programa debe tener al menos 3 caracteres';
    public static $ERROR_DESCRIPTION_EMPTY = 'la descripción del programa no debe ir vacío';
    public static $ERROR_DESCRIPTION_MIN_LENGTH = 'la descripción del programa debe tener al menos 10 caracteres';
    public static $ERROR_CONTACT_INVALID = 'el contacto debe ser una instancia de Contact';
    public static $ERROR_PROGRAM_STATE_INVALID = 'el estado debe ser una instancia de ProgramState';

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
        ?string $bannerImg,
        string $programUrl,
        Contact $contact,
        ProgramState $programState
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

        // Validación de instancias de entidades relacionadas
        if (!($contact instanceof Contact)) {
            throw new RuntimeException(self::$ERROR_CONTACT_INVALID);
        }
        if (!($programState instanceof ProgramState)) {
            throw new RuntimeException(self::$ERROR_PROGRAM_STATE_INVALID);
        }

        return new Program([
            'name' => $trimmedName,
            'description' => $trimmedDescription,
            'banner_img' => $bannerImg ? trim($bannerImg) : null,
            'program_url' => trim($programUrl),
            'contact_id' => $contact->getKey(),
            'program_state_id' => $programState->getKey()
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

    public function getProgramState(): ProgramState
    {
        return $this->programState;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function getSdgs(): array
    {
        return $this->sdgs ? $this->sdgs->all() : [];
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
     * Relación 1:1 con ProgramState
     */
    public function programState()
    {
        return $this->belongsTo(ProgramState::class, 'program_state_id');
    }

    /**
     * Relación M:N con Sdg (a través de tabla pivot program_sdg)
     */
    public function sdgs()
    {
        return $this->belongsToMany(Sdg::class, 'program_sdg', 'program_id', 'sdg_id');
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
