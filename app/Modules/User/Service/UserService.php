<?php

namespace App\Modules\User\Service;

use App\Modules\User\Domain\User;
use App\Modules\User\Repository\UserRepository;
use App\Modules\Role\Repository\RoleRepository;
use App\Modules\UserState\Repository\UserStateRepository;
use RuntimeException;

class UserService
{
    public UserRepository $repository;
    private RoleRepository $roleRepository;
    private UserStateRepository $userStateRepository;

    public function __construct(
        UserRepository $repository,
        RoleRepository $roleRepository,
        UserStateRepository $userStateRepository
    ) {
        $this->repository = $repository;
        $this->roleRepository = $roleRepository;
        $this->userStateRepository = $userStateRepository;
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
        // Recuperar objetos desde repositorios
        $role = $this->roleRepository->findById($roleId);
        if (!$role) {
            throw new RuntimeException("El rol con id {$roleId} no existe.");
        }

        $userState = $this->userStateRepository->findById($userStateId);
        if (!$userState) {
            throw new RuntimeException("El user state con id {$userStateId} no existe.");
        }

        // Crear usuario pasando objetos completos al Domain
        $user = User::at($name, $email, $role, $userState);
        $user->password = $password; // Will be auto-hashed by mutator
        $this->repository->save($user);

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

        // Recuperar objetos desde repositorios
        $role = $this->roleRepository->findById($roleId);
        if (!$role) {
            throw new RuntimeException("El rol con id {$roleId} no existe.");
        }

        $userState = $this->userStateRepository->findById($userStateId);
        if (!$userState) {
            throw new RuntimeException("El user state con id {$userStateId} no existe.");
        }

        // Re-validate domain rules with objects
        $updated = User::at($name, $email, $role, $userState);
        $user->name = $updated->name;
        $user->email = $updated->email;
        $user->role_id = $updated->role_id;
        $user->user_state_id = $updated->user_state_id;

        // Update password only if provided
        if ($password !== null) {
            $user->password = $password; // Will be auto-hashed by mutator
        }

        $this->repository->save($user);

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
