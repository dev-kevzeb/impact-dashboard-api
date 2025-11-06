<?php

namespace App\Modules\Kpa\Domain;
use \App\Modules\Country\Domain\Country;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Kpa extends Model
{

    protected $table = 'kpa';
    protected $fillable = ['name', 'implementation'];


    // Constantes de mensajes de error
    public static $ERROR_NAME_EMPTY = 'el nombre del KPA no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del KPA debe tener al menos 2 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del KPA no debe exceder 100 caracteres';
    public static $ERROR_IMPLEMENTATION_NOT_NUMERIC = 'la implementación del KPA debe ser un número';
    public static $ERROR_IMPLEMENTATION_OUT_OF_RANGE = 'la implementación del KPA debe estar entre 0 y 100';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    
    public static function at(string $name, mixed $implementation): Kpa
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
            throw new RuntimeException(self::$ERROR_IMPLEMENTATION_NOT_NUMERIC);
        }
        if($implementation < 0 || $implementation > 100){
            throw new RuntimeException(self::$ERROR_IMPLEMENTATION_OUT_OF_RANGE);
        }
        return new Kpa(['name' => trim($name), 'implementation' => (float) $implementation]);
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getImplementation(): float
    {
        return $this->implementation;
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class, 'country_kpa', 'id_kpa', 'id_country');
    }
}