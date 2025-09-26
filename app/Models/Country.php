<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Country extends Model
{
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
            throw new RuntimeException('el nombre del país no debe ir vacio');
        }
        
        $trimmedName = trim($name);
        
        if (strlen($trimmedName) < 2) {
            throw new RuntimeException('el nombre del país debe tener al menos 2 caracteres');
        }
        
        if (strlen($trimmedName) > 100) {
            throw new RuntimeException('el nombre del país no debe exceder 100 caracteres');
        }
        
        // Validar que contenga solo letras, espacios, guiones, apostrofes y caracteres unicode válidos
        if (!preg_match('/^[a-zA-ZÀ-ÿñÑ\s\-\'\.]+$/u', $trimmedName)) {
            throw new RuntimeException('el nombre del país contiene caracteres no válidos');
        }
        
        if (!($currency instanceof Currency)) {
            throw new RuntimeException('la moneda debe ser una instancia de Currency');
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
