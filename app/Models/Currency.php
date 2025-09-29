<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Currency extends Model
{
    // Constantes para mensajes de validación
    public const ERROR_CODE_EMPTY = 'el código de moneda no debe ir vacio';
    public const ERROR_CODE_LENGTH = 'el código de moneda debe tener exactamente 3 caracteres';
    public const ERROR_CODE_FORMAT = 'el código de moneda debe contener solo letras mayúsculas';
    public const ERROR_CODE_INVALID = 'el código de moneda debe ser un código ISO 4217 válido';

    private string $code;
    
    // Lista consolidada de códigos ISO 4217 válidos
    private static array $validCodes = [
        // Monedas principales
        'USD', // Dólar estadounidense
        'EUR', // Euro
        'GBP', // Libra esterlina
        'JPY', // Yen japonés
        'CHF', // Franco suizo
        'CAD', // Dólar canadiense
        'AUD', // Dólar australiano
        'CNY', // Yuan chino
        'BOB', // Boliviano
        'BRL', // Real brasileño
        'ARS', // Peso argentino
        'PEN', // Sol peruano
        'CLP', // Peso chileno
        'COP', // Peso colombiano
        'UYU', // Peso uruguayo
        'PYG', // Guaraní paraguayo
        'VES', // Bolívar venezolano
        'CRC', // Colón costarricense
        'GTQ', // Quetzal guatemalteco
        'HNL', // Lempira hondureña
        'NIO', // Córdoba nicaragüense
        'PAB', // Balboa panameña
        'DOP', // Peso dominicano
        'CUP', // Peso cubano
        'HTG', // Gourde haitiana
        'JMD', // Dólar jamaiquino
        'TTD', // Dólar trinitense
        'BBD', // Dólar barbadense
        'MXN', // Peso mexicano
        // Otras monedas importantes
        'KRW', 'SGD', 'HKD', 'THB', 'MYR', 'IDR', 'PHP', 'VND',
        'INR', 'PKR', 'BDT', 'LKR', 'NPR', 'BTN',
        'RUB', 'UAH', 'PLN', 'CZK', 'HUF', 'RON', 'BGN', 'HRK',
        'SEK', 'NOK', 'DKK', 'ISK',
        'ZAR', 'EGP', 'NGN', 'KES', 'GHS', 'MAD', 'TND', 'DZD',
        'XOF', 'XAF', 'ETB', 'UGX', 'TZS', 'RWF', 'ZMW', 'BWP',
        'NAD', 'SZL', 'LSL', 'MWK', 'MZN', 'AOA', 'CVE', 'GMD',
        'GNF', 'LRD', 'SLL', 'STN'
    ];
    
    public function __construct(string $code)
    {
        $this->code = $code;
    }
    
    public static function at($code): Currency
    {
        if (empty(trim($code))) {
            throw new RuntimeException(self::ERROR_CODE_EMPTY);
        }
        
        $trimmedCode = trim($code);
        
        if (strlen($trimmedCode) !== 3) {
            throw new RuntimeException(self::ERROR_CODE_LENGTH);
        }
        
        if (!preg_match('/^[A-Z]{3}$/', $trimmedCode)) {
            throw new RuntimeException(self::ERROR_CODE_FORMAT);
        }
        
        if (!in_array($trimmedCode, self::$validCodes)) {
            throw new RuntimeException(self::ERROR_CODE_INVALID);
        }
        
        return new Currency($trimmedCode);
    }
    
    public function getCode(): string
    {
        return $this->code;
    }
    

}