<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Kpa extends Model
{
    // Constantes de mensajes de error
    public const ERROR_NAME_EMPTY = 'el nombre del KPA no debe ir vacio';
    public const ERROR_NAME_MIN_LENGTH = 'el nombre del KPA debe tener al menos 2 caracteres';
    public const ERROR_NAME_MAX_LENGTH = 'el nombre del KPA no debe exceder 100 caracteres';
    
    private string $name;
    
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    
    public static function at($name): Kpa  
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 2) {
            throw new RuntimeException(self::ERROR_NAME_MIN_LENGTH);
        }
        if (strlen(trim($name)) > 100) {
            throw new RuntimeException(self::ERROR_NAME_MAX_LENGTH);
        }
        
        return new Kpa(trim($name));
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function validateName(): bool
    {
        return !empty(trim($this->name)) && strlen(trim($this->name)) >= 2 && strlen(trim($this->name)) <= 100;
    }
}