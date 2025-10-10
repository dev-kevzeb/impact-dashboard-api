<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Indicator extends Model
{
    // Constantes de mensajes de error
    public static $ERROR_NAME_EMPTY = 'el nombre del indicador no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del indicador debe tener al menos 2 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del indicador no debe exceder 200 caracteres';
    public static $ERROR_TYPE_REQUIRED = 'el tipo de indicador debe ser una instancia de IndicatorType';
    public static $ERROR_TARGET_INVALID = 'el target del indicador debe ser un número positivo';

    // attributes
    private string $name;
    private IndicatorType $type;
    private int $target;

    // constructor
    public function __construct(string $name, IndicatorType $type, int $target)
    {   
        $this->name = $name;
        $this->type = $type;
        $this->target = $target;
    }

    public static function at($name, $type, $target): Indicator 
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 2) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }
        if (strlen(trim($name)) > 200) {
            throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        }
        if (!$type instanceof IndicatorType) {
            throw new RuntimeException(self::$ERROR_TYPE_REQUIRED);
        }
        if (!is_numeric($target) || $target <= 0) {
            throw new RuntimeException(self::$ERROR_TARGET_INVALID);
        }
        return new Indicator(trim($name), $type, $target);
    }

    // getters
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getTarget(): int
    {
        return $this->target;
    }
    public function getType(): IndicatorType
    {
        return $this->type;
    }
}
