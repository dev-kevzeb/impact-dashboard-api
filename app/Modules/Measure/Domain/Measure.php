<?php

namespace App\Modules\Measure\Domain;

use App\Modules\Indicator\Domain\Indicator;
use App\Modules\StrategicOutput\Domain\StrategicOutput;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\MeasureFactory;

use RuntimeException;

class Measure extends Model
{
    use HasFactory;
    protected $table = 'measure';
    protected $fillable = ['name', 'strategic_output_id'];
    protected $appends = ['indicators_count'];

    public static $ERROR_NAME_EMPTY = 'the measure name must not be empty';
    public static $ERROR_NAME_MIN_LENGTH = 'the measure name must be at least 2 characters long';
    public static $ERROR_NAME_MAX_LENGTH = 'the measure name must not exceed 300 characters';
    public static $ERROR_INDICATORS_DUPLICATED = 'duplicate indicators are not allowed in the measure';
    public static $ERROR_INDICATOR_NOT_FOUND = 'the specified indicator does not exist in this measure';

    public static function newFactory()
    {
        return MeasureFactory::new();
    }

    public static function at(string $name, StrategicOutput $strategicOutput): self
    {
        $name = trim($name);

        if ($name === '') throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        if (strlen($name) < 2) throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        if (strlen($name) > 300) throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);

        return new self(['name' => $name, 'strategic_output_id' => $strategicOutput->id]);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addIndicator(Indicator $indicator): void
    {
        if ($this->hasIndicatorWithName($indicator->getName())) throw new RuntimeException(self::$ERROR_INDICATORS_DUPLICATED);
        $this->indicators()->save($indicator);
    }

    public function hasIndicatorWithName(string $name): bool
    {
        return $this->indicators()->where('name', trim($name))->exists();
    }

    public function getIndicatorsCountAttribute(): int
    {
        return $this->indicators()->count();
    }

    public function getIndicators()
    {
        return $this->indicators()->get();
    }

    public function hasIndicators(): bool
    {
        return $this->indicators()->exists();
    }

    public function removeIndicator(string $name): bool
    {
        $found = $this->indicators()->where('name', trim($name))->first();
        if (!$found) return false;

        $found->measure_id = null;
        $found->save();
        return true;
    }

    public function findIndicatorByName(string $name): Indicator
    {
        $indicator = $this->indicators()->where('name', trim($name))->first();
        if (!$indicator) throw new RuntimeException(self::$ERROR_INDICATOR_NOT_FOUND . ': ' . $name);

        return $indicator;
    }



    public function indicators()
    {
        return $this->hasMany(Indicator::class, 'measure_id', 'id');
    }

    public function StrategicOutput()
    {
        return $this->belongsTo(StrategicOutput::class,  'strategic_output_id', 'id');
    }
}
