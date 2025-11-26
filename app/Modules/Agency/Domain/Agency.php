<?php

namespace App\Modules\Agency\Domain;

use App\Modules\Project\Domain\Project;
use App\Modules\ProjectAgency\Domain\ProjectAgency;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use App\Modules\Program\Domain\Program;

class Agency extends Model
{
    protected $table = 'agency';
    protected $fillable = ['name', 'url', 'is_approved'];
    
    // Constantes de mensajes de error
    public static $ERROR_NAME_EMPTY = 'el nombre de la agencia no debe ir vacio';
    public static $ERROR_NAME_TOO_SHORT = 'el nombre de la agencia debe tener al menos 2 caracteres';
    public static $ERROR_NAME_TOO_LONG = 'el nombre de la agencia no debe exceder 100 caracteres';
    public static $ERROR_URL_EMPTY = 'la URL de la agencia no debe ir vacia';
    public static $ERROR_URL_INVALID_FORMAT = 'la URL de la agencia debe tener un formato válido';
    public static $ERROR_URL_INVALID_PROTOCOL = 'la URL de la agencia debe usar protocolo HTTP o HTTPS';
    public static $ERROR_APPROVED_NOT_BOOLEAN = 'el estado de aprobación debe ser un valor booleano';
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    
    public static function at(string $name, string $url, mixed $isApproved): Agency  
    {
        // Validaciones del nombre
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        
        $trimmedName = trim($name);
        
        if (strlen($trimmedName) < 2) {
            throw new RuntimeException(self::$ERROR_NAME_TOO_SHORT);
        }
        
        if (strlen($trimmedName) > 100) {
            throw new RuntimeException(self::$ERROR_NAME_TOO_LONG);
        }
        
        // Validaciones de la URL
        if (empty(trim($url))) {
            throw new RuntimeException(self::$ERROR_URL_EMPTY);
        }
        
        $trimmedUrl = trim($url);
        
        if (!filter_var($trimmedUrl, FILTER_VALIDATE_URL)) {
            throw new RuntimeException(self::$ERROR_URL_INVALID_FORMAT);
        }
        
        $parsedUrl = parse_url($trimmedUrl);
        if (!isset($parsedUrl['scheme']) || !in_array($parsedUrl['scheme'], ['http', 'https'], true)) {
            throw new RuntimeException(self::$ERROR_URL_INVALID_PROTOCOL);
        }
        
        // Validar isApproved
        if (!is_bool($isApproved)) {
            throw new RuntimeException(self::$ERROR_APPROVED_NOT_BOOLEAN);
        }
        
        return new Agency([
            'name' => $trimmedName,
            'url' => $trimmedUrl,
            'is_approved' => $isApproved
        ]);
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getUrl(): string
    {
        return $this->url;
    }
    
    public function isApproved(): bool
    {
        return (bool) $this->is_approved;
    }

    public function projectAgencies()
    {
        return $this->hasMany(ProjectAgency::class, 'agency_id', 'id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_agency', 'agency_id', 'project_id');
    }
}
