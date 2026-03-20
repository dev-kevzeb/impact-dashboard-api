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
    
    public static $ERROR_CODE_EMPTY = 'The currency code should not be empty';
    public static $ERROR_CODE_LENGTH = 'Currency code must be exactly 3 characters';
    public static $ERROR_CODE_FORMAT = 'Currency code must contain only letters (no numbers or symbols)';

    protected static function newFactory()
    {
        return CurrencyFactory::new();
    }
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    
    public static function at(string $code): Currency
    {
        if (empty(trim($code))) throw new RuntimeException(self::$ERROR_CODE_EMPTY);
        $trimmedCode = strtoupper(trim($code));
        
        if (strlen($trimmedCode) !== 3) throw new RuntimeException(self::$ERROR_CODE_LENGTH);
        if (!ctype_alpha($trimmedCode))  throw new RuntimeException(self::$ERROR_CODE_FORMAT);
        
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