<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class ProgramBeneficiary extends Model
{
    // Constantes de mensajes de error
    public const ERROR_NAME_EMPTY = 'el beneficiario del programa no debe ir vacio';
    public const ERROR_NAME_INVALID = 'el beneficiario del programa debe ser: GOVERNMENT, PRIVATE_SECTOR o GOVERNMENT_AND_PRIVATE_SECTOR';
    
    private string $name;
    
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    
    public static function at($name): ProgramBeneficiary
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::ERROR_NAME_EMPTY);
        }
        
        $validBeneficiaries = ['GOVERNMENT', 'PRIVATE_SECTOR', 'GOVERNMENT_AND_PRIVATE_SECTOR'];
        if (!in_array($name, $validBeneficiaries)) {
            throw new RuntimeException(self::ERROR_NAME_INVALID);
        }
        
        return new ProgramBeneficiary($name);
    }
    
    public function validateName(): bool
    {
        $validBeneficiaries = ['GOVERNMENT', 'PRIVATE_SECTOR', 'GOVERNMENT_AND_PRIVATE_SECTOR'];
        return in_array($this->name, $validBeneficiaries);
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function isGovernment(): bool
    {
        return $this->name === 'GOVERNMENT';
    }
    
    public function isPrivateSector(): bool
    {
        return $this->name === 'PRIVATE_SECTOR';
    }
    
    public function isGovernmentAndPrivateSector(): bool
    {
        return $this->name === 'GOVERNMENT_AND_PRIVATE_SECTOR';
    }
}