<?php

namespace App\Modules\Auth\Service;

use App\Modules\User\Domain\User;
use App\Modules\User\Repository\UserRepository;
use App\Modules\UserState\Repository\UserStateRepository;
use RuntimeException;

class AuthService
{
    private UserRepository $userRepository;
    private UserStateRepository $userStateRepository;

    public function __construct(
        UserRepository $userRepository,
        UserStateRepository $userStateRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userStateRepository = $userStateRepository;
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

        // Validate user state
        if (!$user->userState->hasState('active')) {
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
     * Register new user
     *
     * @param string $name
     * @param string $email
     * @param string $password
     * @return array ['access_token', 'token_type', 'expires_in', 'user']
     * @throws RuntimeException
     */
    public function register(string $name, string $email, string $password): array
    {
        // Get default "active" state
        $activeState = $this->userStateRepository->findBy('name', 'active');
        if (!$activeState) {
            throw new RuntimeException('Default user state not found. Run seeders.');
        }

        // Create user with domain factory method
        $user = User::at($name, $email, $activeState);
        $user->password = $password;  // Hashed automatically with mutator
        $this->userRepository->save($user);

        // Generate token for new user
        $token = auth('api')->login($user);

        // Load relationships for UserResource
        $user->load('userState');

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
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
        return auth('api')->user()->load('roles', 'userState');
    }
}
