<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class IndicatorType extends Model
{
    // Constantes de mensajes de error
    public static $ERROR_NAME_EMPTY = 'el nombre del tipo de indicador no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del tipo de indicador debe tener al menos 2 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del tipo de indicador no debe exceder 100 caracteres';
    
    // attributes
    private string $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    
    public static function at($name) : IndicatorType
    {   
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 2) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }
        if (strlen(trim($name)) > 100) {
            throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        }
        return new IndicatorType(trim($name));
    }

    // getters
    public function getName(): string
    {
        return $this->name;
    }
}
