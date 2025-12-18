<?php

namespace App\Modules\Country\Domain;

use App\Modules\Currency\Domain\Currency;
use App\Modules\Kpa\Domain\Kpa;

use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Country extends Model
{
    use HasFactory;
    protected $table = 'country';
    protected $fillable = ['name', 'currency_id'];

    public static $ERROR_NAME_EMPTY = 'The country name must not be empty';
    public static $ERROR_NAME_TOO_SHORT = 'The country name must have at least 2 characters';
    public static $ERROR_NAME_TOO_LONG = 'The country name must not exceed 100 characters';
    public static $ERROR_NAME_INVALID_CHARACTERS = 'The country name contains invalid characters';

    public static $ERROR_CURRENCY_INVALID = 'The currency must be an instance of Currency';

    public static function newFactory()
    {
        return CountryFactory::new();
    }
    
    public static function at($name, $currency): Country
    {
        
        if (empty(trim($name))) throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        $normalizedName = preg_replace('/\s+/', ' ', trim($name));
        if (strlen($normalizedName) < 2) throw new RuntimeException(self::$ERROR_NAME_TOO_SHORT);
    
        if (strlen($normalizedName) > 100) throw new RuntimeException(self::$ERROR_NAME_TOO_LONG);
        if (!preg_match('/^[a-zA-ZÀ-ÿñÑ\s\-\'\.]+$/u', $normalizedName)) throw new RuntimeException(self::$ERROR_NAME_INVALID_CHARACTERS);
        if (!($currency instanceof Currency )) throw new RuntimeException(self::$ERROR_CURRENCY_INVALID);

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

    public function kpas()
    {
        return $this->belongsToMany(Kpa::class, 'country_kpa', 'id_country', 'id_kpa');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
