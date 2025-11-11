<?php

namespace App\Modules\Donor\Domain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Donor extends Model
{
    use HasFactory;
    
    protected $table = 'donor';
    protected $fillable = ['name'];
    
    public static $ERROR_NAME_EMPTY = 'el nombre del donante no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del donante debe tener al menos 2 caracteres';
    
    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\DonorFactory::new();
    }
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    
    public static function at(string $name): Donor  
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 2) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }
        
        return new Donor(['name' => trim($name)]);
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
     * Relación M:N con Program
     * Un donante puede financiar múltiples programas
     */
    public function programs()
    {
        return $this->belongsToMany(\App\Modules\Program\Domain\Program::class, 'program_donor', 'donor_id', 'program_id');
    }
}