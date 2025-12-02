<?php

namespace App\Modules\IndicatorType\Domain;

use Database\Factories\IndicatorTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class IndicatorType extends Model
{
    use HasFactory;
    protected $table = 'indicator_type';
    protected $fillable = ['name'];

    public static $ERROR_NAME_EMPTY = 'el nombre del tipo de indicador no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del tipo de indicador debe tener al menos 2 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del tipo de indicador no debe exceder 100 caracteres';


    protected static function newFactory()
    {
        return IndicatorTypeFactory::new();
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $name): IndicatorType
    {
        if (empty(trim($name))) throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        if (strlen(trim($name)) < 2) throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        if (strlen(trim($name)) > 100) throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        
        return new IndicatorType(['name' => trim($name)]);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
