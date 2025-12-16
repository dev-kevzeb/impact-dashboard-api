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
     * Check if the role matches the given name (case-insensitive comparison)
     * Método genérico y escalable para verificar roles sin hardcodear nombres
     *
     * @param string $roleName Nombre del rol a comparar
     * @return bool True si el rol coincide, false en caso contrario
     */
    public function hasRole(string $roleName): bool
    {
        return strtolower(trim($this->name)) === strtolower(trim($roleName));
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
