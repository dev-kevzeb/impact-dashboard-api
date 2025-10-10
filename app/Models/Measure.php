<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Measure extends Model
{
    // Constantes de mensajes de error
    public static $ERROR_NAME_EMPTY = 'el nombre del Measure no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del Measure debe tener al menos 2 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del Measure no debe exceder 150 caracteres';
    public static $ERROR_INDICATORS_DUPLICATED = 'no se permiten indicadores duplicados en la medida';
    public static $ERROR_INDICATOR_INVALID_INSTANCE = 'el indicador debe ser una instancia de Indicator';
    public static $ERROR_INDICATOR_NOT_FOUND = 'el indicador especificado no existe en esta medida';

    private string $name;
    private array $indicators;
    
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->indicators = []; 
    }
    
    public static function at($name): Measure  
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 2) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }
        if (strlen(trim($name)) > 150) {
            throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        }
        
        return new Measure(trim($name));
    }
    
    public function getName(): string
    {
        return $this->name;
    }

    public function addIndicator($indicator): void
    {
        if (!($indicator instanceof Indicator)) {
            throw new RuntimeException(self::$ERROR_INDICATOR_INVALID_INSTANCE);
        }
        
        // Verificar duplicados por nombre
        if ($this->hasIndicatorWithName($indicator->getName())) {
            throw new RuntimeException(self::$ERROR_INDICATORS_DUPLICATED);
        }
        
        $this->indicators[] = $indicator;
    }

    public function hasIndicatorWithName(string $indicatorName): bool
    {
        foreach ($this->indicators as $indicator) {
            if ($indicator->getName() === $indicatorName) {
                return true;
            }
        }
        return false;
    }
    
    public function getIndicators(): array
    {
        return $this->indicators;
    }
    
    public function getIndicatorCount(): int
    {
        return count($this->indicators);
    }
    
    public function hasIndicators(): bool
    {
        return !empty($this->indicators);
    }
    
    public function removeIndicator(string $indicatorName): bool
    {
        foreach ($this->indicators as $index => $indicator) {
            if ($indicator->getName() === $indicatorName) {
                unset($this->indicators[$index]);
                $this->indicators = array_values($this->indicators); // Re-indexar array
                return true; // Eliminado exitosamente
            }
        }
        return false; // No encontrado
    }
    
    public function clearIndicators(): void
    {
        $this->indicators = [];
    }
    
    public function findIndicatorByName(string $indicatorName): Indicator
    {
        foreach ($this->indicators as $indicator) {
            if ($indicator->getName() === $indicatorName) {
                return $indicator;
            }
        }
        throw new RuntimeException(self::$ERROR_INDICATOR_NOT_FOUND . ': ' . $indicatorName);
    }
}