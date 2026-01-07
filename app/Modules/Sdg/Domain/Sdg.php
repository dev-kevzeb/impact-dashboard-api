<?php


namespace App\Modules\Sdg\Domain;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Sdg extends Model
{
    protected $table = 'sdg';
    protected $fillable = ['image', 'filename'];

    // Error message constants
    public static $ERROR_IMAGE_EMPTY = 'the SDG image name must not be empty';
    public static $ERROR_MISSING_EXTENSION = 'the file must have an extension (examples: .jpg, .png, .gif, .webp, .svg)';
    public static $ERROR_MISSING_FILENAME = 'the file must have a name, not just the extension';
    public static $ERROR_IMAGE_NAME_TOO_LONG = 'the image name cannot exceed 255 characters';
    public static $ERROR_INVALID_CHARACTERS = 'the image name contains invalid characters: < > : " | ? * \\ null';

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
        $filenameBasename = basename($trimmedFilename);
        $filenameExtension = pathinfo($filenameBasename, PATHINFO_EXTENSION);
        if (empty($filenameExtension)) {
            throw new RuntimeException(self::$ERROR_MISSING_EXTENSION);
        }
        $filenameNameWithoutExt = pathinfo($filenameBasename, PATHINFO_FILENAME);
        if (empty(trim($filenameNameWithoutExt))) {
            throw new RuntimeException(self::$ERROR_MISSING_FILENAME);
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

    // ============================================
    // ELOQUENT RELATIONSHIPS
    // ============================================

    /**
     * M:N relationship with Program
     * An SDG can be associated with multiple programs
     */
    public function programs()
    {
        return $this->belongsToMany(\App\Modules\Program\Domain\Program::class, 'program_sdg', 'sdg_id', 'program_id');
    }
}
