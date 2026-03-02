<?php

namespace App\Modules\Role\Service;

use App\Modules\Role\Domain\Role;
use App\Modules\Role\Repository\RoleRepository;
use RuntimeException;

class RoleService
{
    private RoleRepository $repository;

    public function __construct(RoleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new user role
     *
     * @param string $name
     * @return Role
     * @throws RuntimeException
     */
    public function createRole(string $name): Role
    {
        $Role = Role::at($name);
        $this->repository->save($Role);

        return $Role;
    }

    /**
     * Update an existing user role
     *
     * @param int $id
     * @param string $name
     * @return Role
     * @throws RuntimeException
     */
    public function updateRole(int $id, string $name): Role
    {
        $Role = $this->repository->findById($id);

        $updated = Role::at($name);
        $Role->name = $updated->name;
        $this->repository->save($Role);

        return $Role;
    }

    /**
     * Find a user role by name
     *
     * @param string $name
     * @return Role
     * @throws RuntimeException
     */
    public function findRoleByName(string $name): Role
    {
        return $this->repository->findBy('name', $name);
    }

    /**
     * Get all user roles
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllRoles()
    {
        return $this->repository->getAll();
    }

    /**
     * Find user role by ID
     *
     * @param int $id
     * @return Role
     * @throws RuntimeException
     */
    public function findRoleById(int $id): Role
    {
        return $this->repository->findById($id);
    }

    /**
     * Search user roles by name
     *
     * @param string $searchTerm
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchRoles(string $searchTerm)
    {
        // Buscar roles que contengan el término de búsqueda (case-insensitive)
        // Acceso directo al modelo Eloquent para hacer consultas personalizadas
        $model = \App\Modules\Role\Domain\Role::query();

        return $model->whereRaw("LOWER(name) LIKE LOWER(?)", ['%' . trim($searchTerm) . '%'])
            ->get();
    }

    /**
     * Get all permissions for a specific role
     *
     * @param int $roleId
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws RuntimeException
     */
    public function getRolePermissions(int $roleId)
    {
        $role = $this->repository->findById($roleId);
        return $role->permissions;
    }

    /**
     * Assign a permission to a role using Spatie
     *
     * @param int $roleId
     * @param int $permissionId
     * @return Role
     * @throws RuntimeException
     */
    public function assignPermissionToRole(int $roleId, int $permissionId): Role
    {
        $role = $this->repository->findById($roleId);

        // Get the permission by ID
        $permission = \Spatie\Permission\Models\Permission::findOrFail($permissionId);

        // Use Spatie's givePermissionTo method (idempotent - won't duplicate if already assigned)
        $role->givePermissionTo($permission);

        // Refresh to load updated permissions
        $role->refresh();

        return $role;
    }

    /**
     * Remove a permission from a role using Spatie
     *
     * @param int $roleId
     * @param int $permissionId
     * @return Role
     * @throws RuntimeException
     */
    public function removePermissionFromRole(int $roleId, int $permissionId): Role
    {
        $role = $this->repository->findById($roleId);

        // Get the permission by ID
        $permission = \Spatie\Permission\Models\Permission::findOrFail($permissionId);

        // Use Spatie's revokePermissionTo method
        $role->revokePermissionTo($permission);

        // Refresh to load updated permissions
        $role->refresh();

        return $role;
    }
}
