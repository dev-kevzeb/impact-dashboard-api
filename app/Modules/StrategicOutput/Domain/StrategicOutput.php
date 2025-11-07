<?php

namespace App\Modules\StrategicOutput\Domain;

use App\Models\Measure;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class StrategicOutput extends Model
{


    protected $table = 'strategic_output';
    protected $fillable = ['name', 'id_ck'];
    // Constantes de mensajes de error
    public static $ERROR_NAME_EMPTY = 'el nombre del resultado estratégico no debe ir vacío';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del resultado estratégico debe tener al menos 3 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del resultado estratégico no debe exceder 200 caracteres';
    public static $ERROR_MEASURES_DUPLICATED = 'no se permiten medidas duplicadas en el resultado estratégico';
    public static $ERROR_MEASURE_INVALID_INSTANCE = 'la medida debe ser una instancia de Measure';
    public static $ERROR_MEASURE_NOT_FOUND = 'la medida especificada no existe en este resultado estratégico';
    
    public static function at(string $name): StrategicOutput  
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        
        // Normalizar espacios: quitar dobles espacios
        $normalizedName = preg_replace('/\s+/', ' ', trim($name));
        
        if (strlen($normalizedName) < 3) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }
        if (strlen($normalizedName) > 200) {
            throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        }

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
        if (!($measure instanceof Measure)) {
            throw new RuntimeException(self::$ERROR_MEASURE_INVALID_INSTANCE);
        }        
        // Verificar duplicados por nombre
        if ($this->hasMeasureWithName($measure->getName())) {
            throw new RuntimeException(self::$ERROR_MEASURES_DUPLICATED);
        }
        
        $this->measures[] = $measure;
    }

    public function hasMeasureWithName(string $measureName): bool
    {
        foreach ($this->measures as $measure) {
            if ($measure->getName() === $measureName) {
                return true;
            }
        }
        return false;
    }
    
    public function getMeasures(): array
    {
        return $this->measures;
    }
    
    public function getMeasureCount(): int
    {
        return count($this->measures);
    }
    
    public function hasMeasures(): bool
    {
        return !empty($this->measures);
    }

    // Relación con CountryKpa
    public function countryKpa()
    {
        return $this->belongsTo(\App\Modules\CountryKpa\Domain\CountryKpa::class, 'id_ck');
    }
    
    public function removeMeasure(string $measureName): bool
    {
        foreach ($this->measures as $index => $measure) {
            if ($measure->getName() === $measureName) {
                unset($this->measures[$index]);
                $this->measures = array_values($this->measures); // Re-indexar array
                return true; // Eliminado exitosamente
            }
        }
        return false; // No encontrado
    }
    
    public function clearMeasures(): void
    {
        $this->measures = [];
    }
    
    public function findMeasureByName(string $measureName): Measure
    {
        foreach ($this->measures as $measure) {
            if ($measure->getName() === $measureName) {
                return $measure;
            }
        }
        throw new RuntimeException(self::$ERROR_MEASURE_NOT_FOUND . ': ' . $measureName);
    }

    
   
}