<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class IndicatorType extends Model
{
    // attributes
    private string $name;
    static $NAME_MIN_LENGTH = "el nombre del tipo de indicador no debe ser null o menor a 3 caracteres";
    static $NAME_MUST_BE_STRING = "el nombre del tipo de indicador debe ser una cadena de caracteres";
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    // getters
    public function getName(): string
    {
        return $this->name;
    }
    // validators
    public function isString():bool{
        return is_string($this->name);
    }
    public static function at($name) : IndicatorType
    {   
        if (!is_string($name)) {
            throw new RuntimeException(self::$NAME_MUST_BE_STRING);
        }
        $name = trim($name);

        if (strlen($name) < 3) {
            throw new RuntimeException(self::$NAME_MIN_LENGTH);
        }
        return new IndicatorType($name);
    }
}
