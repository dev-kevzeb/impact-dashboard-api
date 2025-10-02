<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Sdg extends Model
{
    // Constantes de mensajes de error
    public static $ERROR_IMAGE_EMPTY = 'la imagen del SDG no debe ir vacio';
    public static $ERROR_INVALID_IMAGE_FILE = 'el archivo debe ser una imagen válida (jpg, jpeg, png, gif, webp, svg)';
    public static $ERROR_IMAGE_NAME_TOO_LONG = 'el nombre de la imagen no puede exceder 255 caracteres';
    public static $ERROR_INVALID_CHARACTERS = 'el nombre de la imagen contiene caracteres no permitidos: < > : " | ? * \\ null';

    private string $image;
    
    public function __construct(string $image)
    {
        $this->image = $image;
    }
    
    public static function at($image): Sdg
    {
        if (empty(trim($image))) {
            throw new RuntimeException(self::$ERROR_IMAGE_EMPTY);
        }
        
        $trimmedImage = trim($image);
        
        if (strlen($trimmedImage) > 255) {
            throw new RuntimeException(self::$ERROR_IMAGE_NAME_TOO_LONG);
        }
        
        if (!self::hasValidCharacters($trimmedImage)) {
            throw new RuntimeException(self::$ERROR_INVALID_CHARACTERS);
        }
        
        if (!self::isValidImageFile($trimmedImage)) {
            throw new RuntimeException(self::$ERROR_INVALID_IMAGE_FILE);
        }
        
        return new Sdg($trimmedImage);
    }
    
    private static function isValidImageFile(string $filename): bool
    {
        $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, $validExtensions);
    }
    
    private static function hasValidCharacters(string $filename): bool
    {
        // Caracteres problemáticos en nombres de archivo
        $dangerousChars = ['<', '>', ':', '"', '|', '?', '*', '\\', "\0"];
        
        foreach ($dangerousChars as $char) {
            if (str_contains($filename, $char)) {
                return false;
            }
        }
        
        return true;
    }
    
    public function getImage(): string
    {
        return $this->image;
    }
}