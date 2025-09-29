<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class StrategicOutput extends Model
{
    // Constantes de mensajes de error
    public const ERROR_NAME_EMPTY = 'el nombre del StrategicOutput no debe ir vacio';
    public const ERROR_NAME_MIN_LENGTH = 'el nombre del StrategicOutput debe tener al menos 2 caracteres';
    public const ERROR_NAME_MAX_LENGTH = 'el nombre del StrategicOutput no debe exceder 200 caracteres';
    public const ERROR_KPA_NULL = 'el KPA no debe ser null';
    public const ERROR_KPA_INVALID = 'el KPA debe ser una instancia de Kpa';
    
    private string $name;
    private Kpa $kpa;
    
    public function __construct(string $name, Kpa $kpa)
    {
        $this->name = $name;
        $this->kpa = $kpa;
    }
    
    public static function at($name, $kpa): StrategicOutput  
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 2) {
            throw new RuntimeException(self::ERROR_NAME_MIN_LENGTH);
        }
        if (strlen(trim($name)) > 200) {
            throw new RuntimeException(self::ERROR_NAME_MAX_LENGTH);
        }
        
        if ($kpa === null) {
            throw new RuntimeException(self::ERROR_KPA_NULL);
        }
        if (!($kpa instanceof Kpa)) {
            throw new RuntimeException(self::ERROR_KPA_INVALID);
        }
        
        return new StrategicOutput(trim($name), $kpa);
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getKpa(): Kpa
    {
        return $this->kpa;
    }
    
    public function validateName(): bool
    {
        return !empty(trim($this->name)) && strlen(trim($this->name)) >= 2 && strlen(trim($this->name)) <= 200;
    }
}