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


    public static function at(string $name): self
    {
        $name = trim($name);

        if ($name === '') {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        if (strlen($name) < 2) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }
        if (strlen($name) > 100) {
            throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        }

        return new self(['name' => $name]);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function hasIndicatorWithName(string $name): bool
    {
        return $this->indicators()->where('name', trim($name))->exists();
    }

    public function addIndicator(Indicator $indicator): void
    {
        if ($this->hasIndicatorWithName($indicator->getName())) throw new RuntimeException(self::$ERROR_INDICATORS_DUPLICATED);
        
        $this->indicators()->save($indicator);
    }
    
    public function removeIndicator(string $name): bool
    {
        $found = $this->indicators()->where('name', trim($name))->first();
        if (!$found) return false;
        
        $found->delete();
        return true;
    }

    public function findIndicatorByName(string $name): Indicator
    {
        $indicator = $this->indicators()->where('name', trim($name))->first();
        if (!$indicator) throw new RuntimeException(self::$ERROR_INDICATOR_NOT_FOUND . ': ' . $name);
        
        return $indicator;
    }

    public function getIndicatorsCountAttribute(): int
    {
        return $this->indicators()->count();
    }

    public function indicators()
    {
        return $this->hasMany(Indicator::class, 'measure_id', 'id');
    }

    public function StrategicOutput()
    {
        return $this->belongsTo( StrategicOutput::class,  'strategic_output_id', 'id');
    }

    // ESTOS 3 METODOS FALTAN IMPLEMENTAR, PREGUNTAR SI DEBEN TENER ENDPOINTS
    public function clearIndicators(): void
    {
        $this->indicators()->delete();
    }   
    public function hasIndicators(): bool
    {
        return $this->indicators()->exists();
    }
    public function getIndicators()
    {
        return $this->indicators()->get();
    }
}