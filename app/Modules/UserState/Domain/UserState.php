<?php

namespace App\Modules\UserState\Domain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class UserState extends Model
{
    use HasFactory;

    protected $table = 'user_state';
    protected $fillable = ['name'];

    // Error constants in Spanish
    public static $ERROR_NAME_EMPTY = 'el nombre del estado no debe ir vacío';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del estado debe tener al menos 2 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del estado no debe exceder 50 caracteres';

    /**
     * Factory method with domain validation
     *
     * @param string $name Estado del usuario (ej: active, inactive, suspended)
     * @throws RuntimeException
     * @return UserState
     */
    public static function at(string $name): UserState
    {
        $trimmedName = trim($name);

        if (empty($trimmedName)) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }

        if (strlen($trimmedName) < 2) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }

        if (strlen($trimmedName) > 50) {
            throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        }

        return new UserState(['name' => $trimmedName]);
    }

    // Getters
    public function getName(): string
    {
        return $this->name;
    }

    // Helper methods for common states
    public function isActive(): bool
    {
        return strtolower($this->name) === 'active';
    }

    public function isInactive(): bool
    {
        return strtolower($this->name) === 'inactive';
    }

    public function isSuspended(): bool
    {
        return strtolower($this->name) === 'suspended';
    }

    /**
     * Laravel Factory integration
     */
    protected static function newFactory()
    {
        return \Database\Factories\UserStateFactory::new();
    }
}
