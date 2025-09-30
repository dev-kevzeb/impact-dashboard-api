<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Country extends Model
{
    // Constantes para mensajes de validación
    public const ERROR_NAME_EMPTY = 'el nombre del país no debe ir vacio';
    public const ERROR_NAME_TOO_SHORT = 'el nombre del país debe tener al menos 2 caracteres';
    public const ERROR_NAME_TOO_LONG = 'el nombre del país no debe exceder 100 caracteres';
    public const ERROR_NAME_INVALID_CHARACTERS = 'el nombre del país contiene caracteres no válidos';
    public const ERROR_CURRENCY_INVALID = 'la moneda debe ser una instancia de Currency';

    private string $name;
    private Currency $currency;
    
    public function __construct(string $name, Currency $currency)
    {
        $this->name = $name;
        $this->currency = $currency;
    }
    
    public static function at($name, $currency): Country
    {
        
        if (empty(trim($name))) {
            throw new RuntimeException(self::ERROR_NAME_EMPTY);
        }
        
        $trimmedName = trim($name);
        
        if (strlen($trimmedName) < 2) {
            throw new RuntimeException(self::ERROR_NAME_TOO_SHORT);
        }
        
        if (strlen($trimmedName) > 100) {
            throw new RuntimeException(self::ERROR_NAME_TOO_LONG);
        }
        
        // Validar que contenga solo letras, espacios, guiones, apostrofes y caracteres unicode válidos
        if (!preg_match('/^[a-zA-ZÀ-ÿñÑ\s\-\'\.]+$/u', $trimmedName)) {
            throw new RuntimeException(self::ERROR_NAME_INVALID_CHARACTERS);
        }
        
        if (!($currency instanceof Currency)) {
            throw new RuntimeException(self::ERROR_CURRENCY_INVALID);
        }        return new Country($trimmedName, $currency);
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
    

}
