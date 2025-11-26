<?php

namespace App\Modules\Beneficiary\Domain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Beneficiary extends Model
{
    use HasFactory;
    
    protected $table = 'beneficiary';
    protected $fillable = ['name'];
    
    public static $ERROR_NAME_EMPTY = 'el nombre del beneficiario no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del beneficiario debe tener al menos 2 caracteres';
    
    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\BeneficiaryFactory::new();
    }
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    
    public static function at(string $name): Beneficiary  
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 2) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }
        
        return new Beneficiary(['name' => trim($name)]);
    }
    
    public function validateName(): bool
    {
        return strlen($this->name) >= 2;
    }
    
    public function getName(): string
    {
        return $this->name;
    }

    // ============================================
    // RELACIONES ELOQUENT
    // ============================================

    /**
     * Relación 1:N con Program
     * Un beneficiario puede tener múltiples programas
     */
    public function programs()
    {
        return $this->hasMany(\App\Modules\Program\Domain\Program::class, 'beneficiary_id');
    }
}
