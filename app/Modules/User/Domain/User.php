<?php

namespace App\Modules\User\Domain;

use App\Modules\Role\Domain\Role;
use App\Modules\UserState\Domain\UserState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use RuntimeException;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'user';
    protected $fillable = ['name', 'email', 'password', 'user_state_id'];
    protected $hidden = ['password', 'remember_token'];

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
     * Use $user->roles to get all roles assigned to the user
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
     * Generate scopes based on user roles
     * 
     * CURRENT IMPLEMENTATION: Hardcoded role-to-scope mapping
     * 
     * WARNING: This is a simple approach for MVP. For production scalability, 
     * consider moving role permissions to database (see RolePermission table approach).
     * 
     * Existing roles in system: admin, project-manager, country-manager
     * Scopes format: {module}:{permission} (e.g., 'donors:read', 'projects:write')
     * 
     * @return array Array of scope strings
     */
    private function generateScopes(): array
    {
        $scopes = [];

        // Load roles if not already loaded
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        foreach ($this->roles as $role) {
            $roleName = strtolower($role->name);

            // ADMIN - Full system access (God mode)
            if ($roleName === 'admin') {
                return ['*:*'];  // Wildcard = all permissions on all modules
            }

            // PROJECT-MANAGER - Manages projects, programs, beneficiaries, indicators
            if ($roleName === 'project-manager') {
                $scopes = array_merge($scopes, [
                    // Projects module
                    'projects:read',
                    'projects:write',

                    // Programs module
                    'programs:read',
                    'programs:write',

                    // Beneficiaries module
                    'beneficiaries:read',
                    'beneficiaries:write',

                    // Donors (read-only)
                    'donors:read',

                    // Agencies, Contacts, SDGs (read/write)
                    'projects:read',  // Already included above

                    // Indicators, Measures, Strategic Outputs
                    'projects:read',  // Already included above
                ]);
            }

            // COUNTRY-MANAGER - Manages KPAs, countries, users assignments
            if ($roleName === 'country-manager') {
                $scopes = array_merge($scopes, [
                    // KPAs module
                    'kpas:read',
                    'kpas:write',

                    // Programs (read/write)
                    'programs:read',
                    'programs:write',

                    // Projects (read-only)
                    'projects:read',

                    // Users management
                    'users:read',
                    'users:write',

                    // Donors, Beneficiaries (read-only)
                    'donors:read',
                    'beneficiaries:read',
                ]);
            }
        }

        // If no roles assigned, return minimal read-only access
        if (empty($scopes)) {
            return ['donors:read', 'beneficiaries:read'];
        }

        // Remove duplicates and return
        return array_values(array_unique($scopes));
    }
}
