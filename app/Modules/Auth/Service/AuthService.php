<?php

namespace App\Modules\Auth\Service;

use App\Modules\Country\Repository\CountryRepository;
use App\Modules\User\Domain\User;
use App\Modules\User\Repository\UserRepository;
use App\Modules\UserRole\Domain\UserRole;
use App\Modules\UserState\Repository\UserStateRepository;
use Spatie\Permission\Models\Role;
use RuntimeException;

class AuthService
{
    private UserRepository $userRepository;
    private UserStateRepository $userStateRepository;
    private CountryRepository $countryRepository;

    public function __construct(
        UserRepository $userRepository,
        UserStateRepository $userStateRepository,
        CountryRepository $countryRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userStateRepository = $userStateRepository;
        $this->countryRepository = $countryRepository;
    }

    /**
     * Authenticate user and generate JWT token
     *
     * @param string $email
     * @param string $password
     * @return array ['access_token', 'token_type', 'expires_in', 'user']
     * @throws RuntimeException
     */
    public function login(string $email, string $password): array
    {
        $credentials = ['email' => $email, 'password' => $password];

        // Attempt to authenticate with JWT
        if (!$token = auth('api')->attempt($credentials)) {
            throw new RuntimeException('Invalid credentials');
        }

        $user = auth('api')->user();

        // Check email verification
        if (!$user->hasVerifiedEmail()) {
            throw new RuntimeException('Please verify your email before logging in. Check your inbox.');
        }

        // Validate user state with specific messages
        $stateName = $user->userState->name;

        if ($stateName === 'unverified') {
            throw new RuntimeException('Please verify your email before logging in. Check your inbox.');
        }

        if ($stateName === 'pending') {
            throw new RuntimeException('Your account is pending approval. Please wait for admin confirmation.');
        }

        if ($stateName === 'inactive') {
            throw new RuntimeException('Your account has been deactivated. Contact support for assistance.');
        }

        if ($stateName !== 'active') {
            throw new RuntimeException('User account is not active');
        }

        // Load relationships needed for UserResource
        $user->load('roles', 'userState');

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,  // Seconds
            'user' => new \App\Http\Resources\UserResource($user)
        ];
    }

    /**
     * Register new user with unverified state
     * 
     * Users register with unverified state and must verify email before admin approval.
     * Only project-manager and country-manager roles are allowed for self-registration.
     *
     * @param string $name
     * @param string $email
     * @param string $password
     * @param string $roleName Role name (project-manager or country-manager)
     * @param int $countryId Country ID for initial assignment
     * @return array ['message', 'user'] - NO token until email verified and admin approves
     * @throws RuntimeException
     */
    public function register(string $name, string $email, string $password, string $roleName, int $countryId): array
    {
        // Validate allowed roles for self-registration
        $allowedRoles = ['project-manager', 'country-manager'];
        if (!in_array($roleName, $allowedRoles)) {
            throw new RuntimeException(
                'Invalid role. Only project-manager and country-manager are allowed for registration.'
            );
        }

        // Get unverified state (not pending)
        $unverifiedState = $this->userStateRepository->findBy('name', 'unverified');
        if (!$unverifiedState) {
            throw new RuntimeException('Unverified state not found. Run UserStateSeeder.');
        }

        // Validate role exists
        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            throw new RuntimeException("Role {$roleName} not found. Run RoleSeeder.");
        }

        // Validate country exists
        $country = $this->countryRepository->findById($countryId);
        if (!$country) {
            throw new RuntimeException('Country not found.');
        }

        // Create user with unverified state
        $user = User::at($name, $email, $unverifiedState);
        $user->password = $password;  // Hashed automatically with mutator
        $this->userRepository->save($user);

        // Assign selected role (creates user_role via Spatie)
        $user->assignRole($role);

        // Get the UserRole record that was just created
        $userRole = UserRole::where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->first();

        // Assign country to user role via pivot table
        if ($userRole) {
            $userRole->countries()->attach($countryId);
        }

        // Send email verification notification
        $user->sendEmailVerificationNotification();

        // Load relationships for response (including countries)
        $user->load('roles', 'userState', 'userRoles.countryUserRole.country', 'userRoles.countryUserRole.userRole.role');

        // NO token generated - user cannot login until email verified and admin approves
        return [
            'message' => 'Registration successful. Please check your email to verify your account.',
            'user' => new \App\Http\Resources\UserResource($user)
        ];
    }

    /**
     * Refresh expired token
     * 
     * Important: This regenerates scopes from current user roles.
     * If roles changed since last login, new token will have updated scopes.
     *
     * @return array ['access_token', 'token_type', 'expires_in']
     */
    public function refresh(): array
    {
        // Refresh token (this calls getJWTCustomClaims() to regenerate scopes)
        $newToken = auth('api')->refresh();

        return [
            'access_token' => $newToken,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ];
    }

    /**
     * Get authenticated user
     *
     * @return User
     */
    public function me(): User
    {
        return auth('api')->user()->load('roles', 'userState', 'userRoles.countryUserRole.country', 'userRoles.countryUserRole.userRole.role');
    }
}
