<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Sdg extends Model
{
    private $image;
    private $number;
    
    public function __construct(string $image, int $number = null)
    {
        $this->image = $image;
        $this->number = $number;
    }
    
    public static function at($image): Sdg
    {
        if ($image === null) {
            throw new RuntimeException('la imagen del SDG no debe ser null');
        }
        if (strlen((string)$image) == 0) {
            throw new RuntimeException('la imagen del SDG no debe ir vacio');
        }
        
        // Extraer número del SDG desde el nombre de imagen (ej: "sdg1.png" -> 1)
        $number = null;
        if (preg_match('/sdg(\d+)/i', $image, $matches)) {
            $number = (int)$matches[1];
            if ($number < 1 || $number > 17) {
                throw new RuntimeException('el número del SDG debe estar entre 1 y 17');
            }
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
    
    public function getNumber(): ?int
    {
        return $this->number;
    }
    
    public function isValidSdgNumber(): bool
    {
        return $this->number !== null && $this->number >= 1 && $this->number <= 17;
    }
}