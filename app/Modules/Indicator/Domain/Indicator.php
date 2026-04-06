<?php

namespace App\Modules\Indicator\Domain;

use App\Modules\IndicatorType\Domain\IndicatorType;
use App\Modules\Measure\Domain\Measure;
use App\Modules\Project\Domain\Project;
use App\Modules\ProjectIndicator\Domain\ProjectIndicator;
use Database\Factories\IndicatorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class  Indicator extends Model
{
    use HasFactory;
    // Associated table
    protected $table = 'indicator';

    // Allowed fields
    protected $fillable = ['name', 'type_id', 'target', 'actual_value', 'measure_id'];

    // Error constants
    public static $ERROR_NAME_EMPTY = 'the indicator name must not be empty';
    public static $ERROR_NAME_MIN_LENGTH = 'the indicator name must be at least 2 characters long';
    public static $ERROR_NAME_MAX_LENGTH = 'the indicator name must not exceed 300 characters';
    public static $ERROR_TYPE_REQUIRED = 'the indicator type must be an instance of IndicatorType';
    public static $ERROR_TARGET_INVALID = 'the indicator target must be a positive number';
    public static $ERROR_MEASURE_REQUIRED = 'the measure must be an instance of Measure';

    public static function newFactory()
    {
        return IndicatorFactory::new();
    }
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $name, $type, $target, $measure): Indicator
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 2) throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);

        if (strlen(trim($name)) > 300) throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);

        if (!$type instanceof IndicatorType) throw new RuntimeException(self::$ERROR_TYPE_REQUIRED);

        if (!$measure instanceof Measure) throw new RuntimeException(self::$ERROR_MEASURE_REQUIRED);

        if (!is_numeric($target) || $target <= 0) throw new RuntimeException(self::$ERROR_TARGET_INVALID);

        return new Indicator(['name' => trim($name), 'type_id' => $type->id, 'target' => $target, 'measure_id' => $measure->id]);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTarget(): float
    {
        return $this->target;
    }

    public function getType(): IndicatorType
    {
        return $this->type;
    }

    public function type()
    {
        return $this->belongsTo(IndicatorType::class, 'type_id', 'id');
    }

    public function measure()
    {
        return $this->belongsTo(Measure::class,  'measure_id', 'id');
    }

    public function projectIndicators()
    {
        return $this->hasMany(ProjectIndicator::class, 'indicator_id', 'id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_indicator', 'indicator_id', 'project_id');
    }
}
