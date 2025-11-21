<?php

namespace App\Modules\ProjectState\Domain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class ProjectState extends Model
{
    use HasFactory;

    protected $table = 'project_state';
    protected $fillable = ['state'];

    // Error constants in Spanish
    public static $ERROR_NAME_EMPTY = 'el nombre del estado del proyecto no debe ir vacío';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del estado del proyecto debe tener al menos 3 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del estado del proyecto no debe exceder 100 caracteres';

    // Factory method with domain validation
    public static function at(string $name): ProjectState
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 3) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }
        if (strlen(trim($name)) > 100) {
            throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        }
        return new ProjectState(['state' => trim($name)]);
    }

    public function getState(): string
    {
        return $this->state;
    }

    // Laravel Factory integration (required for testing)
    protected static function newFactory()
    {
        return \Database\Factories\ProjectStateFactory::new();
    }
}
