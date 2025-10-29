<?php

namespace App\Modules\Country\Domain;

use App\Modules\Currency\Domain\Currency;
use App\Modules\Kpa\Domain\Kpa;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Country extends Model
{
    protected $table = 'country';
    protected $fillable = ['name', 'currency_id'];

    // Constantes para mensajes de validación
    public static $ERROR_NAME_EMPTY = 'el nombre del país no debe ir vacio';
    public static $ERROR_NAME_TOO_SHORT = 'el nombre del país debe tener al menos 2 caracteres';
    public static $ERROR_NAME_TOO_LONG = 'el nombre del país no debe exceder 100 caracteres';
    public static $ERROR_NAME_INVALID_CHARACTERS = 'el nombre del país contiene caracteres no válidos';
    public static $ERROR_CURRENCY_INVALID = 'la moneda debe ser una instancia de Currency';
    public static $ERROR_KPA_MUST_BE_ARRAY = 'los KPAs deben estar en un array';
   

    public static function at($name, $currency): Country
    {
        
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
        
        // Validar que contenga solo letras, espacios, guiones, apostrofes y caracteres unicode válidos
        if (!preg_match('/^[a-zA-ZÀ-ÿñÑ\s\-\'\.]+$/u', $trimmedName)) {
            throw new RuntimeException(self::$ERROR_NAME_INVALID_CHARACTERS);
        }
        
        if (!($currency instanceof Currency )) {
            throw new RuntimeException(self::$ERROR_CURRENCY_INVALID);
        }

        return new self(['name' => $trimmedName, 'currency_id' => $currency->getKey()]);
    }

    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getCurrency(): Currency
    {
        return $this->currency;
    }
    
    public function getCurrencyCode(): string
    {
        return $this->currency->getCode();
    }
    
    public function getKpas(): array
    {
        return $this->kpa;
    }
     
    // relaciones Eloquent (persistencia)
    public function kpas()
    {
        // definimos la relacion N:M con Kpa, usando la tabla pivote 'country_kpas'
        return $this->belongsToMany(Kpa::class, 'country_kpas', 'id_country', 'id_kpa');
    }

    public function currency()
    {
        // definimos la relacion 1:1 con Currency
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
