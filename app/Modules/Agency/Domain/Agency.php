<?php

namespace App\Modules\Agency\Domain;

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
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre de la agencia debe tener al menos 2 caracteres';
    public static $ERROR_NAME_TOO_LONG = 'el nombre de la agencia no debe exceder 100 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre de la agencia no debe exceder 100 caracteres';
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
    
    public function validateName(): bool
    {
        return strlen($this->name) >= 2 && strlen($this->name) <= 100;
    }
    
    public function validateUrl(): bool
    {
        if (!filter_var($this->url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        $parsedUrl = parse_url($this->url);
        return isset($parsedUrl['scheme']) && in_array($parsedUrl['scheme'], ['http', 'https'], true);
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getUrl(): string
    {
        return $this->url;
    }
    
    public function getIsApproved(): bool
    {
        return (bool) $this->is_approved;
    }
    
    public function isApproved(): bool
    {
        return (bool) $this->is_approved;
    }
    
    public function compareIsApproved(bool $isApproved): bool
    {
        return $this->is_approved === $isApproved;
    }
    
    public function programs()
    {
        return $this->hasMany(Program::class, 'agency_id');
    }
}
