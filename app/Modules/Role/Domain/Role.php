<?php

namespace App\Modules\Role\Domain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Role extends Model
{
    use HasFactory;

    protected $table = 'role';
    protected $fillable = ['name'];

    // Error constants in Spanish
    public static $ERROR_NAME_EMPTY = 'el nombre del rol no debe ir vacío';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del rol debe tener al menos 2 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del rol no debe exceder 50 caracteres';

    /**
     * Factory method with domain validation
     *
     * @param string $name
     * @return Role
     * @throws RuntimeException
     */
    public static function at(string $name): Role
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

        return new Role(['name' => $trimmedName]);
    }

    /**
     * Get the name of the role
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Check if role is admin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->name === 'admin';
    }

    /**
     * Check if role is country manager
     *
     * @return bool
     */
    public function isCountryManager(): bool
    {
        return $this->name === 'country_manager';
    }

    /**
     * Check if role is project manager
     *
     * @return bool
     */
    public function isProjectManager(): bool
    {
        return $this->name === 'project_manager';
    }

    /**
     * Laravel Factory integration
     *
     * @return \Database\Factories\RoleFactory
     */
    protected static function newFactory()
    {
        return \Database\Factories\RoleFactory::new();
    }
}
