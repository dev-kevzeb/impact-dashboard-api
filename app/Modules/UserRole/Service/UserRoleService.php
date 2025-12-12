<?php

namespace App\Modules\UserRole\Service;

use App\Modules\UserRole\Domain\UserRole;
use App\Modules\UserRole\Repository\UserRoleRepository;
use RuntimeException;

class UserRoleService
{
    private UserRoleRepository $repository;

    public function __construct(UserRoleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new user role
     *
     * @param string $name
     * @return UserRole
     * @throws RuntimeException
     */
    public function createUserRole(string $name): UserRole
    {
        $userRole = UserRole::at($name);
        $this->repository->save($userRole);

        return $userRole;
    }

    /**
     * Update an existing user role
     *
     * @param int $id
     * @param string $name
     * @return UserRole
     * @throws RuntimeException
     */
    public function updateUserRole(int $id, string $name): UserRole
    {
        $userRole = $this->repository->findById($id);

        $updated = UserRole::at($name);
        $userRole->name = $updated->name;
        $this->repository->save($userRole);

        return $userRole;
    }

    /**
     * Find a user role by name
     *
     * @param string $name
     * @return UserRole
     * @throws RuntimeException
     */
    public function findUserRoleByName(string $name): UserRole
    {
        return $this->repository->findBy('name', $name);
    }

    /**
     * Get all user roles
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllUserRoles()
    {
        return $this->repository->getAll();
    }

    /**
     * Find user role by ID
     *
     * @param int $id
     * @return UserRole
     * @throws RuntimeException
     */
    public function findUserRoleById(int $id): UserRole
    {
        return $this->repository->findById($id);
    }

    /**
     * Search user roles by name
     *
     * @param string $searchTerm
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchUserRoles(string $searchTerm)
    {
        // Buscar roles que contengan el término de búsqueda (case-insensitive)
        // Acceso directo al modelo Eloquent para hacer consultas personalizadas
        $model = \App\Modules\UserRole\Domain\UserRole::query();
        
        return $model->whereRaw("LOWER(name) LIKE LOWER(?)", ['%' . trim($searchTerm) . '%'])
                     ->get();
    }
}
