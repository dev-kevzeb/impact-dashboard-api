<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Kpa extends Model
{
    // Constantes de mensajes de error
    public static $ERROR_NAME_EMPTY = 'el nombre del KPA no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del KPA debe tener al menos 2 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del KPA no debe exceder 100 caracteres';
    public static $ERROR_IMPLEMENTATION_NOT_NUMERIC = 'la implementación del KPA debe ser un número';
    public static $ERROR_IMPLEMENTATION_OUT_OF_RANGE = 'la implementación del KPA debe estar entre 0 y 100';
    public static $ERROR_STRATEGIC_OUTPUTS_DUPLICATED = 'no se permiten resultados estratégicos duplicados en el KPA';
    public static $ERROR_STRATEGIC_OUTPUT_INVALID_INSTANCE = 'el resultado estratégico debe ser una instancia de StrategicOutput';
    private string $name;
    private float $implementation;
    private array $strategicOutputs = [];
    public function __construct(string $name, float $implementation = 0)
    {
        $this->name = $name;
        $this->implementation = $implementation;
        $this->strategicOutputs = []; 
    }

    public static function at($name, $implementation): Kpa
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 2) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }
        if (strlen(trim($name)) > 100) {
            throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        }
        if(!is_numeric($implementation)){
            throw new RuntimeException(self::$ERROR_IMPLEMENTATION_NOT_NUMERIC);
        }
        if($implementation < 0 || $implementation > 100){
            throw new RuntimeException(self::$ERROR_IMPLEMENTATION_OUT_OF_RANGE);
        }
        return new Kpa(trim($name), $implementation);
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getImplementation(): float
    {
        return $this->implementation;
    }
    
    public function addStrategicOutput($strategicOutput): void
    {
        if (!($strategicOutput instanceof StrategicOutput)) {
            throw new RuntimeException(self::$ERROR_STRATEGIC_OUTPUT_INVALID_INSTANCE);
        }
        
        // Verificar duplicados por nombre
        if ($this->hasStrategicOutputWithName($strategicOutput->getName())) {
            throw new RuntimeException(self::$ERROR_STRATEGIC_OUTPUTS_DUPLICATED);
        }
        
        $this->strategicOutputs[] = $strategicOutput;
    }

    private function hasStrategicOutputWithName(string $outputName): bool
    {
        foreach ($this->strategicOutputs as $existingOutput) {
            if ($existingOutput->getName() === $outputName) {
                return true;
            }
        }
        return false;
    }
    
    public function getStrategicOutputs(): array
    {
        return $this->strategicOutputs;
    }
    
    public function getStrategicOutputCount(): int
    {
        return count($this->strategicOutputs);
    }
    
    public function hasStrategicOutputs(): bool
    {
        return !empty($this->strategicOutputs);
    }
    
    public function removeStrategicOutput(string $outputName): bool
    {
        foreach ($this->strategicOutputs as $index => $output) {
            if ($output->getName() === $outputName) {
                unset($this->strategicOutputs[$index]);
                $this->strategicOutputs = array_values($this->strategicOutputs); // Re-indexar array
                return true; // Eliminado exitosamente
            }
        }
        return false; // No encontrado
    }
    
    public function clearStrategicOutputs(): void
    {
        $this->strategicOutputs = [];
    }
    
    public function findStrategicOutputByName(string $outputName): ?StrategicOutput
    {
        foreach ($this->strategicOutputs as $output) {
            if ($output->getName() === $outputName) {
                return $output;
            }
        }
        return null;
    }
}