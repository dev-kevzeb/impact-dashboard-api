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
        // Normalizar espacios: quitar dobles espacios y espacios al inicio/fin
        $normalizedName = preg_replace('/\s+/', ' ', trim($name));
        if (strlen($normalizedName) < 2) {
            throw new RuntimeException(self::$ERROR_NAME_TOO_SHORT);
        }
        if (strlen($normalizedName) > 100) {
            throw new RuntimeException(self::$ERROR_NAME_TOO_LONG);
        }
        // Validar caracteres permitidos
        if (!preg_match('/^[a-zA-ZÀ-ÿñÑ\s\-\'\.]+$/u', $normalizedName)) {
            throw new RuntimeException(self::$ERROR_NAME_INVALID_CHARACTERS);
        }
        if (!($currency instanceof Currency )) {
            throw new RuntimeException(self::$ERROR_CURRENCY_INVALID);
        }
        // Capitalizar cada palabra
        $capitalizedName = mb_convert_case($normalizedName, MB_CASE_TITLE, "UTF-8");
        return new self(['name' => $capitalizedName, 'currency_id' => $currency->getKey()]);
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
