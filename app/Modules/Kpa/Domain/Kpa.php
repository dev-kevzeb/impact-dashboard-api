<?php

namespace App\Modules\Kpa\Domain;
use \App\Modules\Country\Domain\Country;
use App\Modules\CountryKpa\Domain\CountryKpa;
use App\Modules\StrategicOutput\Domain\StrategicOutput;
use Database\Factories\KpaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Kpa extends Model
{

    use HasFactory;
    protected $table = 'kpa';
    protected $fillable = ['name', 'implementation'];

    public static $ERROR_NAME_EMPTY = 'The KPA name must not be empty';
    public static $ERROR_NAME_MIN_LENGTH = 'The KPA name must have at least 2 characters';
    public static $ERROR_NAME_MAX_LENGTH = 'The KPA name must not exceed 100 characters';

    public static $ERROR_IMPLEMENTATION_NOT_NUMERIC = 'The KPA implementation must be a number';
    public static $ERROR_IMPLEMENTATION_OUT_OF_RANGE = 'The KPA implementation must be between 0 and 100';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function newFactory()
    {
        return KpaFactory::new();
    }
    
    public static function at(string $name, mixed $implementation): Kpa
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 2) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }
        if (strlen(trim($name)) > 100) {
            throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        }
        if(!is_numeric($implementation)){
            throw new RuntimeException(self::$ERROR_IMPLEMENTATION_NOT_NUMERIC);
        }
        if($implementation < 0 || $implementation > 100){
            throw new RuntimeException(self::$ERROR_IMPLEMENTATION_OUT_OF_RANGE);
        }
        return new Kpa(['name' => trim($name), 'implementation' => (float) $implementation]);
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getImplementation(): float
    {
        return $this->implementation;
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class, 'country_kpa', 'id_kpa', 'id_country');
    }

    public function strategicOutputs()
    {
        return $this->hasManyThrough(StrategicOutput::class,CountryKpa::class,'id_kpa','id_ck','id','id');
    }

}