<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Measure extends Model
{
    // Constantes de mensajes de error
    public static $ERROR_NAME_EMPTY = 'el nombre del Measure no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del Measure debe tener al menos 2 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del Measure no debe exceder 150 caracteres';
    public static $ERROR_STRATEGIC_OUTPUT_NULL = 'el StrategicOutput no debe ser null';
    public static $ERROR_STRATEGIC_OUTPUT_INVALID = 'el StrategicOutput debe ser una instancia de StrategicOutput';

    private string $name;
    private StrategicOutput $strategicOutput;
    
    public function __construct(string $name, StrategicOutput $strategicOutput)
    {
        $this->name = $name;
        $this->strategicOutput = $strategicOutput;
    }
    
    public static function at($name, $strategicOutput): Measure  
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 2) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }
        if (strlen(trim($name)) > 150) {
            throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        }
        
        if ($strategicOutput === null) {
            throw new RuntimeException(self::$ERROR_STRATEGIC_OUTPUT_NULL);
        }
        if (!($strategicOutput instanceof StrategicOutput)) {
            throw new RuntimeException(self::$ERROR_STRATEGIC_OUTPUT_INVALID);
        }
        
        return new Measure(trim($name), $strategicOutput);
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getStrategicOutput(): StrategicOutput
    {
        return $this->strategicOutput;
    }
}