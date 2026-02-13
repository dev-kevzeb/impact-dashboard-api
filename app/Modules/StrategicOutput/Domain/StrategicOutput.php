<?php

namespace App\Modules\StrategicOutput\Domain;


use App\Modules\Measure\Domain\Measure;
use Database\Factories\StrategicOutputFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \App\Modules\CountryKpa\Domain\CountryKpa;
use RuntimeException;

class StrategicOutput extends Model
{
    use HasFactory;
    protected $table = 'strategic_output';
    protected $fillable = ['name', 'id_ck'];
    protected $appends = ['measures_count'];

    public static $ERROR_NAME_EMPTY = 'The strategic output name must not be empty';
    public static $ERROR_NAME_MIN_LENGTH = 'The strategic output name must have at least 3 characters';
    public static $ERROR_NAME_MAX_LENGTH = 'The strategic output name must not exceed 300 characters';

    public static $ERROR_MEASURES_DUPLICATED = 'Duplicate measures are not allowed in the strategic output';
    public static $ERROR_MEASURE_INVALID_INSTANCE = 'The measure must be an instance of Measure';
    public static $ERROR_MEASURE_NOT_FOUND = 'The specified measure does not exist in this strategic output';

    public static function newFactory()
    {
        return StrategicOutputFactory::new();
    }
    
    public static function at(string $name): StrategicOutput  
    {
        if (empty(trim($name))) throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        
        // Normalizar espacios: quitar dobles espacios
        $normalizedName = preg_replace('/\s+/', ' ', trim($name));
        
        if (strlen($normalizedName) < 2) throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        if (strlen($normalizedName) > 300) throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        
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

    public function hasMeasures(): bool
    {
        return $this->measures()->exists();
    }
    
    public function getMeasures()
    {
        return $this->measures()->get();
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
        return $this->belongsTo(CountryKpa::class, 'id_ck', 'id');
    }

    public function measures()
    {
        return $this->hasMany(Measure::class, 'strategic_output_id', 'id');
    }
     
}