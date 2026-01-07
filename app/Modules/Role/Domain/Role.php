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

    // Error constants
    public static $ERROR_NAME_EMPTY = 'the role name must not be empty';
    public static $ERROR_NAME_MIN_LENGTH = 'the role name must be at least 2 characters';
    public static $ERROR_NAME_MAX_LENGTH = 'the role name must not exceed 50 characters';

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
     * Generic and scalable method to verify roles without hardcoding names
     *
     * @param string $roleName Role name to compare
     * @return bool True if the role matches, false otherwise
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
