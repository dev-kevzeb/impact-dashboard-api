<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Kpa extends Model
{
    // Constantes de mensajes de error
    public static $ERROR_NAME_EMPTY = 'el nombre del KPA no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del KPA debe tener al menos 2 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del KPA no debe exceder 100 caracteres';
    public static $IMPLEMENTATION_MUST_BE_NUMERIC = "la implementación del KPA debe ser un número";
    public static $STRATEGICOUTPUTS_MUST_BE_ARRAY = "los resultados estratégicos del KPA deben estar en un array";
    public static $IMPLEMENTATION_MUST_BE_BETWEEN_0_AND_100 = "la implementación del KPA debe estar entre 0 y 100";
    private string $name;
    private float $implementation;
    private array $strategicOutputs = [];
    public function __construct(string $name, float $implementation = 0, array $strategicOutputs = [])
    {
        $this->name = $name;
        $this->implementation = $implementation;
        $this->strategicOutputs = $strategicOutputs;
    }

    public static function at($name, $implementation, $strategicOutputs): Kpa
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
        if(!is_numeric($implementation)){
            throw new RuntimeException(self::$IMPLEMENTATION_MUST_BE_NUMERIC);
        }
        if($implementation < 0 || $implementation > 100){
            throw new RuntimeException(self::$IMPLEMENTATION_MUST_BE_BETWEEN_0_AND_100);
        }
        if(!is_array($strategicOutputs)){
            throw new RuntimeException(self::$STRATEGICOUTPUTS_MUST_BE_ARRAY);
        }
        return new Kpa(trim($name), $implementation, $strategicOutputs);
    }
    
    public function getName(): string
    {
        return $this->name;
    }
}