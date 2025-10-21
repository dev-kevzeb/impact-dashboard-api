<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class StrategicOutput extends Model
{
    // Constantes de mensajes de error
    public static $ERROR_NAME_EMPTY = 'el nombre del StrategicOutput no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del StrategicOutput debe tener al menos 2 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del StrategicOutput no debe exceder 200 caracteres';
    public static $ERROR_KPA_INVALID = 'el KPA debe ser una instancia de Kpa';
    public static $ERROR_MEASURES_DUPLICATED = 'no se permiten medidas duplicadas en el resultado estratégico';
    public static $ERROR_MEASURE_INVALID_INSTANCE = 'la medida debe ser una instancia de Measure';
    public static $ERROR_MEASURE_NOT_FOUND = 'la medida especificada no existe en este resultado estratégico';

    private string $name;
    private Kpa $kpa;
    private array $measures;
    
    public function __construct(string $name, Kpa $kpa)
    {
        $this->name = $name;
        $this->kpa = $kpa;
        $this->measures = []; 
    }
    
    public static function at($name, $kpa): StrategicOutput  
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 2) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }
        if (strlen(trim($name)) > 200) {
            throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        }
        
        if (!($kpa instanceof Kpa)) {
            throw new RuntimeException(self::$ERROR_KPA_INVALID);
        }
        
        return new StrategicOutput(trim($name), $kpa);
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getKpa(): Kpa
    {
        return $this->kpa;
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
        throw new Exception('Medida no encontrada');
    }
}