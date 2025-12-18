<?php

namespace App\Modules\User\Domain;

use App\Modules\Role\Domain\Role;
use App\Modules\UserState\Domain\UserState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'user';
    protected $fillable = ['name', 'email', 'password', 'role_id', 'user_state_id'];
    protected $hidden = ['password', 'remember_token'];

    // Error constants in Spanish
    public static $ERROR_NAME_EMPTY = 'el nombre no debe ir vacío';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre debe tener al menos 2 caracteres';
    public static $ERROR_EMAIL_EMPTY = 'el email no debe ir vacío';
    public static $ERROR_EMAIL_INVALID = 'el email no es válido';
    public static $ERROR_ROLE_INVALID = 'el role debe ser una instancia de Role';
    public static $ERROR_USER_STATE_INVALID = 'el user state debe ser una instancia de UserState';

    /**
     * Mutator: Hash password automatically when assigned
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Factory method with domain validation
     *
     * @param string $name
     * @param string $email
     * @param Role $role
     * @param UserState $userState
     * @return User
     * @throws RuntimeException
     */
    public static function at(string $name, string $email, Role $role, UserState $userState): User
    {
        $trimmedName = trim($name);
        $trimmedEmail = trim($email);

        if (empty($trimmedName)) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }

        if (strlen($trimmedName) < 2) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }

        if (empty($trimmedEmail)) {
            throw new RuntimeException(self::$ERROR_EMAIL_EMPTY);
        }

        if (!filter_var($trimmedEmail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(self::$ERROR_EMAIL_INVALID);
        }

        if (!($role instanceof Role)) {
            throw new RuntimeException(self::$ERROR_ROLE_INVALID);
        }

        if (!($userState instanceof UserState)) {
            throw new RuntimeException(self::$ERROR_USER_STATE_INVALID);
        }

        return new User([
            'name' => $trimmedName,
            'email' => strtolower($trimmedEmail),
            'role_id' => $role->id,
            'user_state_id' => $userState->id
        ]);
    }

    /**
     * Get the user's name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the user's email
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Relationship: User belongs to a Role
     *
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relationship: User belongs to a UserState
     *
     * @return BelongsTo
     */
    public function userState(): BelongsTo
    {
        return $this->belongsTo(UserState::class);
    }

    /**
     * Laravel Factory integration
     *
     * @return \Database\Factories\UserFactory
     */
    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }
}
