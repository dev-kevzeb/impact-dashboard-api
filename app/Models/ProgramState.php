<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class ProgramState extends Model
{
    // Constantes de mensajes de error
    public const ERROR_STATE_EMPTY = 'el estado del programa no debe ir vacio';
    public const ERROR_STATE_INVALID = 'el estado del programa debe ser: ACTIVE, INACTIVE o COMPLETE';
    
    // Constante para estados válidos
    public const VALID_STATES = ['ACTIVE', 'INACTIVE', 'COMPLETE'];
    
    private string $state;
    
    public function __construct(string $state)
    {
        $this->state = $state;
    }
    
    public static function at($state): ProgramState
    {
        if (empty(trim($state))) {
            throw new RuntimeException(self::ERROR_STATE_EMPTY);
        }
        
        if (!in_array($state, self::VALID_STATES)) {
            throw new RuntimeException(self::ERROR_STATE_INVALID);
        }
        
        return new ProgramState($state);
    }
    
    public function getState(): string
    {
        return $this->state;
    }
    
    public function isActive(): bool
    {
        return $this->state === 'ACTIVE';
    }
    
    public function isInactive(): bool
    {
        return $this->state === 'INACTIVE';
    }
    
    public function isComplete(): bool
    {
        return $this->state === 'COMPLETE';
    }
}