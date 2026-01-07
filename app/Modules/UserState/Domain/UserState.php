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

    // Error constants
    public static $ERROR_NAME_EMPTY = 'the state name must not be empty';
    public static $ERROR_NAME_MIN_LENGTH = 'the state name must be at least 2 characters';
    public static $ERROR_NAME_MAX_LENGTH = 'the state name must not exceed 50 characters';

    /**
     * Factory method with domain validation
     *
     * @param string $name User state (e.g., active, inactive, suspended)
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

    /**
     * Get the name of the user state
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Check if the user state matches the given name (case-insensitive comparison)
     * Generic and scalable method to verify states without hardcoding names
     *
     * @param string $stateName State name to compare
     * @return bool True if the state matches, false otherwise
     */
    public function hasState(string $stateName): bool
    {
        return strtolower(trim($this->name)) === strtolower(trim($stateName));
    }

    /**
     * Laravel Factory integration
     */
    protected static function newFactory()
    {
        return \Database\Factories\UserStateFactory::new();
    }
}
