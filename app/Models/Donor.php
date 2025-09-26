<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Donor extends Model
{
    private string $name;
    
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    
    public static function at($name): Donor  
    {
        if (empty(trim($name))) {
            throw new RuntimeException('el nombre del donante no debe ir vacio');
        }
        if (strlen(trim($name)) < 2) {
            throw new RuntimeException('el nombre del donante debe tener al menos 2 caracteres');
        }
        
        return new Donor($name);
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