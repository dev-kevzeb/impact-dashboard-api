<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class ProgramState extends Model
{
    private string $state;
    
    public function __construct(string $state)
    {
        $this->state = $state;
    }
    
    public static function at($state): ProgramState
    {
        if (empty(trim($state))) {
            throw new RuntimeException('el estado del programa no debe ir vacio');
        }
        
        $validStates = ['ACTIVE', 'INACTIVE', 'COMPLETE'];
        if (!in_array($state, $validStates)) {
            throw new RuntimeException('el estado del programa debe ser: ACTIVE, INACTIVE o COMPLETE');
        }
        
        return new ProgramState($state);
    }
    
    public function validateState(): bool
    {
        $validStates = ['ACTIVE', 'INACTIVE', 'COMPLETE'];
        return in_array($this->state, $validStates);
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