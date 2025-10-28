<?php

namespace App\Modules\Beneficiary\Domain;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Beneficiary extends Model
{
    protected $table = 'beneficiary';
    protected $fillable = ['name'];
    
    public static $ERROR_NAME_EMPTY = 'el nombre del beneficiario no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del beneficiario debe tener al menos 2 caracteres';
    
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
}
