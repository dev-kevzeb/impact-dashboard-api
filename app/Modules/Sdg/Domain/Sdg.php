<?php


namespace App\Modules\Sdg\Domain;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Sdg extends Model
{
    protected $table = 'sdg';
    protected $fillable = ['image', 'filename'];

    // Constantes de mensajes de error
    public static $ERROR_IMAGE_EMPTY = 'el nombre de la imagen del SDG no debe ir vacío';
    public static $ERROR_MISSING_EXTENSION = 'el archivo debe tener una extensión (ejemplos: .jpg, .png, .gif, .webp, .svg)';
    public static $ERROR_MISSING_FILENAME = 'el archivo debe tener un nombre, no solo la extensión';
    public static $ERROR_IMAGE_NAME_TOO_LONG = 'el nombre de la imagen no puede exceder 255 caracteres';
    public static $ERROR_INVALID_CHARACTERS = 'el nombre de la imagen contiene caracteres no permitidos: < > : " | ? * \\ null';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $imagePath, string $filename): Sdg
    {
        if (empty(trim($imagePath))) {
            throw new RuntimeException(self::$ERROR_IMAGE_EMPTY);
        }
        $trimmedImage = trim($imagePath);
        if (strlen($trimmedImage) > 255) {
            throw new RuntimeException(self::$ERROR_IMAGE_NAME_TOO_LONG);
        }
        if (!self::hasValidCharacters($trimmedImage)) {
            throw new RuntimeException(self::$ERROR_INVALID_CHARACTERS);
        }
        $filenameBasename = basename($trimmedImage);
        $extension = pathinfo($filenameBasename, PATHINFO_EXTENSION);
        if (empty($extension)) {
            throw new RuntimeException(self::$ERROR_MISSING_EXTENSION);
        }
        $nameWithoutExt = pathinfo($filenameBasename, PATHINFO_FILENAME);
        if (empty(trim($nameWithoutExt))) {
            throw new RuntimeException(self::$ERROR_MISSING_FILENAME);
        }
        // Validar el filename
        if (empty(trim($filename))) {
            throw new RuntimeException(self::$ERROR_IMAGE_EMPTY);
        }
        $trimmedFilename = trim($filename);
        if (strlen($trimmedFilename) > 255) {
            throw new RuntimeException(self::$ERROR_IMAGE_NAME_TOO_LONG);
        }
        if (!self::hasValidCharacters($trimmedFilename)) {
            throw new RuntimeException(self::$ERROR_INVALID_CHARACTERS);
        }
        return new Sdg(['image' => $trimmedImage, 'filename' => $trimmedFilename]);
    }

    private static function hasValidCharacters(string $filename): bool
    {
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

    public function getFilename(): string
    {
        return $this->filename;
    }
}
