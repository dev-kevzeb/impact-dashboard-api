<?php

namespace App\Modules\User\Domain;

use App\Modules\Role\Domain\Role;
use App\Modules\UserState\Domain\UserState;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use RuntimeException;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasFactory, HasRoles, Notifiable;

    protected $table = 'user';
    protected $fillable = ['name', 'email', 'email_verified_at', 'password', 'user_state_id'];
    protected $hidden = ['password', 'remember_token'];

    /**
     * Cast attributes to native types
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Spatie Permission: Define guard name for permissions
     * Must match guard_name in role and permission tables
     */
    protected $guard_name = 'api';

    // Error constants
    public static $ERROR_NAME_EMPTY = 'the name must not be empty';
    public static $ERROR_NAME_MIN_LENGTH = 'the name must be at least 2 characters';
    public static $ERROR_EMAIL_EMPTY = 'the email must not be empty';
    public static $ERROR_EMAIL_INVALID = 'the email is not valid';
    public static $ERROR_USER_STATE_INVALID = 'the user state must be an instance of UserState';

    /**
     * Mutator: Hash password automatically when assigned
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Factory method with domain validation
     * Note: Role assignment is now handled via UserRole table
     *
     * @param string $name
     * @param string $email
     * @param UserState $userState
     * @return User
     * @throws RuntimeException
     */
    public static function at(string $name, string $email, UserState $userState): User
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

        if (!($userState instanceof UserState)) {
            throw new RuntimeException(self::$ERROR_USER_STATE_INVALID);
        }

        return new User([
            'name' => $trimmedName,
            'email' => strtolower($trimmedEmail),
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
     * Relationship: User has many Roles through user_role pivot table
     * 
     * NOTE: This relationship is now managed by Spatie's HasRoles trait.
     * The trait provides methods like:
     * - $user->hasRole('admin')
     * - $user->assignRole('project-manager')
     * - $user->removeRole('country-manager')
     * - $user->getRoleNames()
     * 
     * This manual relationship definition is kept for backwards compatibility
     * with existing code that uses $user->roles->pluck('name').
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role', 'user_id', 'role_id')
            ->withTimestamps();
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
     * Relationship: User has many UserRoles (explicit)
     * Used to access countries through UserRole pivot
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userRoles()
    {
        return $this->hasMany(\App\Modules\UserRole\Domain\UserRole::class, 'user_id');
    }

    /**
     * Get all countries assigned to this user through their roles
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAssignedCountries()
    {
        return $this->userRoles()
            ->with('countries')
            ->get()
            ->pluck('countries')
            ->flatten()
            ->unique('id');
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

    /**
     * Get the identifier that will be stored in the JWT subject claim.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     * 
     * Returns user scopes based on assigned roles.
     * Scopes format: {module}:{permission}
     * 
     * This method is called during token generation (login/register/refresh).
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'scopes' => $this->generateScopes()
        ];
    }

    /**
     * Generate scopes based on user roles and permissions from database
     * 
     * SPATIE PERMISSION INTEGRATION: Reads permissions from database
     * 
     * Uses Spatie's HasRoles trait to fetch permissions:
     * - Admin role: Checks for wildcard permission '*:*'
     * - Other roles: Gets all permissions assigned via role_permission table
     * 
     * Permissions are cached by Spatie for performance.
     * 
     * @return array Array of scope strings (e.g., ['donors:read', 'projects:write'])
     */
    private function generateScopes(): array
    {
        // Check if user has admin role with wildcard permission
        // hasPermissionTo() is provided by HasRoles trait
        if ($this->hasPermissionTo('*:*')) {
            return ['*:*'];  // Full system access
        }

        // Get all permissions for this user (via roles)
        // getAllPermissions() returns Collection of Permission models
        $permissions = $this->getAllPermissions();

        // Extract permission names (scope format)
        // pluck('name') gets the 'name' column from each Permission
        // unique() removes duplicates if user has multiple roles with same permission
        // values() resets array keys to sequential numbers
        $scopes = $permissions->pluck('name')->unique()->values()->toArray();

        // Return scopes as-is (empty array if no permissions assigned)
        // Users without roles/permissions will have no access to protected endpoints
        return $scopes;
    }

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified(): bool
    {
        $this->email_verified_at = $this->freshTimestamp();
        return $this->save();
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification);
    }
}
