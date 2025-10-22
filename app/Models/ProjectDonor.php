<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class ProjectDonor extends Model
{
    // Constantes de mensajes de error
    public static $ERROR_DONOR_INVALID = 'el donante debe ser una instancia de Donor';
    public static $ERROR_CONTRIBUTION_NOT_NUMERIC = 'la contribución debe ser un número';
    public static $ERROR_CONTRIBUTION_OUT_OF_RANGE = 'la contribución debe estar entre 0 y 100';
    
    private Donor $donor;
    private float $contribution;
    
    public function __construct(Donor $donor, float $contribution)
    {
        $this->donor = $donor;
        $this->contribution = $contribution;
    }
    
    public static function at($donor, $contribution): ProjectDonor
    {
        if (!($donor instanceof Donor)) {
            throw new RuntimeException(self::$ERROR_DONOR_INVALID);
        }
        
        if (!is_numeric($contribution)) {
            throw new RuntimeException(self::$ERROR_CONTRIBUTION_NOT_NUMERIC);
        }
        
        $contributionFloat = (float) $contribution;
        
        if ($contributionFloat < 0 || $contributionFloat > 100) {
            throw new RuntimeException(self::$ERROR_CONTRIBUTION_OUT_OF_RANGE);
        }
        
        return new ProjectDonor($donor, $contributionFloat);
    }
    
    public function getDonor(): Donor
    {
        return $this->donor;
    }
    
    public function getContribution(): float
    {
        return $this->contribution;
    }
    
    public function getDonorName(): string
    {
        return $this->donor->getName();
    }
}