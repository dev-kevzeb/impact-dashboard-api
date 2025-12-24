<?php

namespace App\Modules\User\Service;

use App\Modules\User\Domain\User;
use App\Modules\User\Repository\UserRepository;
use App\Modules\Role\Repository\RoleRepository;
use App\Modules\UserState\Repository\UserStateRepository;
use App\Modules\UserRole\Service\UserRoleService;
use RuntimeException;

class UserService
{
    public UserRepository $repository;
    private RoleRepository $roleRepository;
    private UserStateRepository $userStateRepository;
    private UserRoleService $userRoleService;

    public function __construct(
        UserRepository $repository,
        RoleRepository $roleRepository,
        UserStateRepository $userStateRepository,
        UserRoleService $userRoleService
    ) {
        $this->repository = $repository;
        $this->roleRepository = $roleRepository;
        $this->userStateRepository = $userStateRepository;
        $this->userRoleService = $userRoleService;
    }

    /**
     * Create a new User
     *
     * @param string $name
     * @param string $email
     * @param string $password
     * @param int $roleId
     * @param int $userStateId
     * @return User
     * @throws RuntimeException
     */
    public function createUser(string $name, string $email, string $password, int $roleId, int $userStateId): User
    {
        // Validate role exists
        $role = $this->roleRepository->findById($roleId);
        if (!$role) {
            throw new RuntimeException("The role with id {$roleId} does not exist.");
        }

        $userState = $this->userStateRepository->findById($userStateId);
        if (!$userState) {
            throw new RuntimeException("The user state with id {$userStateId} does not exist.");
        }

        // Create user WITHOUT role (now handled via UserRole)
        $user = User::at($name, $email, $userState);
        $user->password = $password; // Will be auto-hashed by mutator
        $this->repository->save($user);

        // Assign role via UserRole pivot table
        $this->userRoleService->createAssignment($user->id, $roleId);

        return $user;
    }

    /**
     * Update an existing User
     *
     * @param int $id
     * @param string $name
     * @param string $email
     * @param int $roleId
     * @param int $userStateId
     * @param string|null $password
     * @return User
     * @throws RuntimeException
     */
    public function updateUser(int $id, string $name, string $email, int $roleId, int $userStateId, ?string $password = null): User
    {
        $user = $this->repository->findById($id);

        // Validate role exists
        $role = $this->roleRepository->findById($roleId);
        if (!$role) {
            throw new RuntimeException("The role with id {$roleId} does not exist.");
        }

        $userState = $this->userStateRepository->findById($userStateId);
        if (!$userState) {
            throw new RuntimeException("The user state with id {$userStateId} does not exist.");
        }

        // Re-validate domain rules
        $updated = User::at($name, $email, $userState);
        $user->name = $updated->name;
        $user->email = $updated->email;
        $user->user_state_id = $updated->user_state_id;

        // Update password only if provided
        if ($password !== null) {
            $user->password = $password; // Will be auto-hashed by mutator
        }

        $this->repository->save($user);

        // Update role via UserRole: remove old roles and assign new one
        // Get current role assignments
        $currentRoles = $this->userRoleService->getRolesByUser($user->id);
        
        // Check if user already has this role
        $hasRole = $currentRoles->contains(function($userRole) use ($roleId) {
            return $userRole->role_id === $roleId;
        });

        if (!$hasRole) {
            // Remove all current roles
            foreach ($currentRoles as $userRole) {
                $this->userRoleService->removeAssignment($userRole->id);
            }
            
            // Assign new role
            $this->userRoleService->createAssignment($user->id, $roleId);
        }

        return $user;
    }

    /**
     * Find a User by name
     *
     * @param string $name
     * @return User
     * @throws RuntimeException
     */
    public function findUserByName(string $name): User
    {
        return $this->repository->findBy('name', $name);
    }

    /**
     * Find a User by email
     *
     * @param string $email
     * @return User|null
     */
    public function findUserByEmail(string $email): ?User
    {
        return $this->repository->findByEmail($email);
    }

    /**
     * Get all users with pagination and relationships
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllUsers(int $perPage = 10)
    {
        return $this->repository->paginateWithRelations($perPage);
    }

    /**
     * Get user by ID with relationships
     *
     * @param int $id
     * @return User
     * @throws RuntimeException
     */
    public function getUserById(int $id): User
    {
        return $this->repository->findByIdWithRelations($id);
    }
}
