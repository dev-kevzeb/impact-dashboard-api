<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Beneficiary extends Model
{
    // Constantes de mensajes de error
    public static $ERROR_NAME_EMPTY = 'el beneficiario no debe ir vacio';
    public static $ERROR_NAME_INVALID = 'el beneficiario debe ser: GOVERNMENT, PRIVATE_SECTOR o GOVERNMENT_AND_PRIVATE_SECTOR';
    
    private string $name;
    
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    
    public static function at($name): Beneficiary
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        
        $validBeneficiaries = ['GOVERNMENT', 'PRIVATE_SECTOR', 'GOVERNMENT_AND_PRIVATE_SECTOR'];
        if (!in_array($name, $validBeneficiaries)) {
            throw new RuntimeException(self::$ERROR_NAME_INVALID);
        }
        
        return new Beneficiary($name);
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