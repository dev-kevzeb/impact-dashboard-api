<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Sdg extends Model
{
    private string $image;
    private int $number;
    
    public function __construct(string $image, int $number)
    {
        $this->image = $image;
        $this->number = $number;
    }
    
    public static function at($image): Sdg
    {
        if (empty(trim($image))) {
            throw new RuntimeException('la imagen del SDG no debe ir vacio');
        }
        
        // Extraer número del SDG es OBLIGATORIO
        if (!preg_match('/sdg(\d+)/i', $image, $matches)) {
            throw new RuntimeException('la imagen del SDG debe contener un número válido (ej: sdg1.png)');
        }
        
        $number = (int)$matches[1];
        if ($number < 1 || $number > 17) {
            throw new RuntimeException('el número del SDG debe estar entre 1 y 17');
        }
        
        return new Sdg($image, $number);
    }
    
    public function validateImage(): bool
    {
        return strlen($this->image) > 0;
    }
    
    public function getImage(): string
    {
        return $this->image;
    }
    
    public function getNumber(): int
    {
        return $this->number;
    }
    
    public function isValidSdgNumber(): bool
    {
        return $this->number >= 1 && $this->number <= 17;
    }
}