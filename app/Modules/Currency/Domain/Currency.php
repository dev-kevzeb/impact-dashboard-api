<?php

namespace App\Modules\Currency\Domain;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Currency extends Model
{
    protected $table = 'currency';
    protected $fillable = ['code'];
   
   
    // Validación en el boot del modelo
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($currency) {
            $currency->validateCode($currency->code);
        });
    }
    
 
    
    // Lista de códigos ISO 4217 válidos
    private static array $validCodes = [
        'USD', 'EUR', 'GBP', 'JPY', 'CHF', 'CAD', 'AUD', 'CNY', 'BOB', 
        'BRL', 'ARS', 'PEN', 'CLP', 'COP', 'UYU', 'PYG', 'VES', 'CRC', 
        'GTQ', 'HNL', 'NIO', 'PAB', 'DOP', 'CUP', 'HTG', 'JMD', 'TTD', 
        'BBD', 'MXN', 'KRW', 'SGD', 'HKD', 'THB', 'MYR', 'IDR', 'PHP', 
        'VND', 'INR', 'PKR', 'BDT', 'LKR', 'NPR', 'BTN', 'RUB', 'UAH', 
        'PLN', 'CZK', 'HUF', 'RON', 'BGN', 'HRK', 'SEK', 'NOK', 'DKK', 
        'ISK', 'ZAR', 'EGP', 'NGN', 'KES', 'GHS', 'MAD', 'TND', 'DZD',
        'XOF', 'XAF', 'ETB', 'UGX', 'TZS', 'RWF', 'ZMW', 'BWP', 'NAD', 
        'SZL', 'LSL', 'MWK', 'MZN', 'AOA', 'CVE', 'GMD', 'GNF', 'LRD', 
        'SLL', 'STN', 'SOL'
    ];
    
    public function validateCode(?string $code): void
    {
        if (empty(trim($code ?? ''))) {
            throw new \InvalidArgumentException('El código de moneda no debe ir vacío');
        }
        
        $trimmedCode = trim($code);
        
        if (strlen($trimmedCode) !== 3) {
            throw new \InvalidArgumentException('El código de moneda debe tener exactamente 3 caracteres');
        }
        
        if (!preg_match('/^[A-Z]{3}$/', $trimmedCode)) {
            throw new \InvalidArgumentException('El código de moneda debe contener solo letras mayúsculas');
        }
        
        if (!in_array($trimmedCode, self::$validCodes, true)) {
            throw new \InvalidArgumentException('El código de moneda debe ser un código ISO 4217 válido');
        }
    }
    
    // Accessor para asegurar que siempre esté en mayúsculas
    protected function code(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
            set: fn ($value) => strtoupper(trim($value))
        );
    }
    
    public static function isValidCode(string $code): bool
    {
        return in_array(strtoupper(trim($code)), self::$validCodes, true);
    }
    public function countries()
    {
        return $this->hasMany(\App\Modules\Country\Domain\Country::class, 'currency_id');
    }
}