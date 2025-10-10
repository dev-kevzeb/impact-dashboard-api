<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Country extends Model
{
    // Constantes para mensajes de validación
    public static $ERROR_NAME_EMPTY = 'el nombre del país no debe ir vacio';
    public static $ERROR_NAME_TOO_SHORT = 'el nombre del país debe tener al menos 2 caracteres';
    public static $ERROR_NAME_TOO_LONG = 'el nombre del país no debe exceder 100 caracteres';
    public static $ERROR_NAME_INVALID_CHARACTERS = 'el nombre del país contiene caracteres no válidos';
    public static $ERROR_CURRENCY_INVALID = 'la moneda debe ser una instancia de Currency';
    public static $ERROR_KPA_MUST_BE_ARRAY = 'los KPAs deben estar en un array';
    private string $name;
    private Currency $currency;
    private array $kpa = [];
    static $INVALIDNAME = 'el nombre del país no debe ir vacio';
    public function __construct(string $name, Currency $currency, array $kpa)
    {
        $this->name = $name;
        $this->currency = $currency;
        $this->kpa = $kpa;
    }

    public static function at($name, $currency, $kpa): Country
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
        
        if (!($currency instanceof Currency)) {
            throw new RuntimeException(self::$ERROR_CURRENCY_INVALID);
        }
        if (!is_array($kpa)) {
            throw new RuntimeException(self::$ERROR_KPA_MUST_BE_ARRAY);
        }

        if (count($kpa) === 0) {
            throw new RuntimeException('debe haber al menos un KPA en el array de KPAs');
        }
        foreach ($kpa as $item) {
            if (!($item instanceof Kpa)) {
                throw new RuntimeException('cada KPA debe ser una instancia de Kpa');
            }
        }
   
        return new Country($trimmedName, $currency, $kpa);
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
}
