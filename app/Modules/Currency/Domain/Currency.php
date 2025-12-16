<?php

namespace App\Modules\Currency\Domain;

use Database\Factories\CurrencyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Currency extends Model
{

    use HasFactory;
    protected $table = 'currency';
    protected $fillable = ['code'];
    
    // Constantes de mensajes de error
    public static $ERROR_CODE_EMPTY = 'The currency code should not be empty';
    public static $ERROR_CODE_LENGTH = 'Currency code must be exactly 3 characters';
    public static $ERROR_CODE_FORMAT = 'Currency code must contain only letters (no numbers or symbols)';
    public static $ERROR_CODE_INVALID = 'The currency code must be a valid ISO 4217 code';

    protected static function newFactory()
    {
        return CurrencyFactory::new();
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
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    
    public static function at(string $code): Currency
    {
        if (empty(trim($code))) {
            throw new RuntimeException(self::$ERROR_CODE_EMPTY);
        }
        
        $trimmedCode = strtoupper(trim($code));
        
        if (strlen($trimmedCode) !== 3) {
            throw new RuntimeException(self::$ERROR_CODE_LENGTH);
        }
        
        // Validar que solo contenga letras (no números ni símbolos)
        if (!ctype_alpha($trimmedCode)) {
            throw new RuntimeException(self::$ERROR_CODE_FORMAT);
        }
        
        if (!in_array($trimmedCode, self::$validCodes, true)) {
            throw new RuntimeException(self::$ERROR_CODE_INVALID);
        }
        
        return new Currency(['code' => $trimmedCode]);
    }
    
    public function getCode(): string
    {
        return $this->code;
    }
    
    public function countries()
    {
        return $this->hasMany(\App\Modules\Country\Domain\Country::class, 'currency_id');
    }
}