<?php

namespace App\Modules\StrategicOutput\Domain;


use App\Modules\Measure\Domain\Measure;
use Illuminate\Database\Eloquent\Model;
use \App\Modules\CountryKpa\Domain\CountryKpa;
use RuntimeException;

class StrategicOutput extends Model
{
    protected $table = 'strategic_output';
    protected $fillable = ['name', 'id_ck'];
    protected $appends = ['measures_count'];

    // Constantes de mensajes de error
    public static $ERROR_NAME_EMPTY = 'el nombre del resultado estratégico no debe ir vacío';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del resultado estratégico debe tener al menos 3 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del resultado estratégico no debe exceder 200 caracteres';
    public static $ERROR_MEASURES_DUPLICATED = 'no se permiten medidas duplicadas en el resultado estratégico';
    public static $ERROR_MEASURE_INVALID_INSTANCE = 'la medida debe ser una instancia de Measure';
    public static $ERROR_MEASURE_NOT_FOUND = 'la medida especificada no existe en este resultado estratégico';
    
    public static function at(string $name): StrategicOutput  
    {
        if (empty(trim($name))) throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        // Normalizar espacios: quitar dobles espacios
        $normalizedName = preg_replace('/\s+/', ' ', trim($name));
        
        if (strlen($normalizedName) < 3) throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        if (strlen($normalizedName) > 200) throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        
        // Capitalizar primera letra de cada palabra
        $capitalizedName = mb_convert_case($normalizedName, MB_CASE_TITLE, "UTF-8");
        
        return new self(['name' => $capitalizedName]);
    }
    
    public function getName(): string
    {
        return $this->name;
    }

    public function addMeasure($measure): void
    {
        if (!($measure instanceof Measure)) throw new RuntimeException(self::$ERROR_MEASURE_INVALID_INSTANCE);
        if ($this->hasMeasureWithName($measure->getName())) throw new RuntimeException(self::$ERROR_MEASURES_DUPLICATED);
        
        $this->measures()->save($measure);
    }

    public function removeMeasure(string $measureName): bool
    {
        $measure = $this->measures()->where('name', trim($measureName))->first();
        if (!$measure) throw new RuntimeException(self::$ERROR_MEASURE_NOT_FOUND . ': ' . $measureName);

        $measure->delete();
        return true;
    }

    public function hasMeasureWithName(string $measureName): bool
    {
        return $this->measures()->where('name', trim($measureName))->exists();
    }
    

    public function getMeasuresCountAttribute(): int
    {
        return $this->measures()->count();
    }

    public function findMeasureByName(string $measureName): Measure
    {
        $measure = $this->measures()->where('name', trim($measureName))->first();
        if (!$measure) throw new RuntimeException(self::$ERROR_MEASURE_NOT_FOUND . ': ' . $measureName);

        return $measure;
    }

    public function countryKpa()
    {
        return $this->belongsTo(CountryKpa::class, 'id_ck');
    }

    public function measures()
    {
        return $this->hasMany(Measure::class, 'strategic_output_id', 'id');
    }
     
    // ESTOS 3 METODOS FALTAN IMPLEMENTAR, PREGUNTAR SI DEBEN TENER ENDPOINTS
    public function clearMeasures(): void
    {
        $this->measures->delete();
    }   
    public function hasMeasures(): bool
    {
        return $this->measures()->exists();
    }
    public function getMeasures()
    {
        return $this->measures()->get();
    }
}