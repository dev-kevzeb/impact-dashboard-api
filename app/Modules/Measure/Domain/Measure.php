<?php
namespace App\Modules\Measure\Domain;

use App\Modules\Indicator\Domain\Indicator;
use App\Modules\StrategicOutput\Domain\StrategicOutput;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Measure extends Model
{
    protected $table = 'measure';
    protected $fillable = ['name', 'strategic_output_id'];
    protected $appends = ['indicators_count'];

    public static $ERROR_NAME_EMPTY = 'el nombre del Measure no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del Measure debe tener al menos 2 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del Measure no debe exceder 150 caracteres';
    public static $ERROR_INDICATORS_DUPLICATED = 'no se permiten indicadores duplicados en la medida';
    public static $ERROR_INDICATOR_NOT_FOUND = 'el indicador especificado no existe en esta medida';

    public static function at(string $name, StrategicOutput $strategicOutput): self
    {
        $name = trim($name);

        if ($name === '') throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        if (strlen($name) < 2) throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        if (strlen($name) > 100) throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);

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


    
    // RELACIONES
    public function indicators()
    {
        return $this->hasMany(Indicator::class, 'measure_id', 'id');
    }

    public function StrategicOutput()
    {
        return $this->belongsTo( StrategicOutput::class,  'strategic_output_id', 'id');
    }
}