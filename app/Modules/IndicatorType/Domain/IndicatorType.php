<?php

namespace App\Modules\IndicatorType\Domain;

use App\Modules\Indicator\Domain\Indicator;
use Database\Factories\IndicatorTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

class IndicatorType extends Model
{
    use HasFactory;
    protected $table = 'indicator_type';
    protected $fillable = ['name', 'is_bottom_up'];

    public static $ERROR_NAME_EMPTY = 'The indicator type name must not be empty';
    public static $ERROR_NAME_MIN_LENGTH = 'The indicator type name must have at least 2 characters';
    public static $ERROR_NAME_MAX_LENGTH = 'The indicator type name must not exceed 100 characters';


    protected static function newFactory()
    {
        return IndicatorTypeFactory::new();
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $name, bool $isBottomUp = true): IndicatorType
    {
        if (empty(trim($name))) throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        if (strlen(trim($name)) < 2) throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        if (strlen(trim($name)) > 100) throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        
        return new IndicatorType(['name' => trim($name), 'is_bottom_up' => $isBottomUp]);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(Indicator::class, 'type_id', 'id');
    }
}
