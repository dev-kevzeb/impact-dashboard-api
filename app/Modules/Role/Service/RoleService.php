<?php

namespace App\Modules\Role\Service;

use App\Modules\Role\Domain\Role;
use App\Modules\Role\Repository\RoleRepository;
use RuntimeException;
use Spatie\Permission\Models\Permission;

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
        $Role = $this->repository->findManageableById($id);

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
        return $this->repository->getManageableRoles();
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
        return $this->repository->findManageableById($id);
    }

    /**
     * Search user roles by name
     *
     * @param string $searchTerm
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchRoles(string $searchTerm)
    {
        return $this->repository->searchManageableRoles($searchTerm);
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
        $role = $this->repository->findManageableById($roleId);
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
        $role = $this->repository->findManageableById($roleId);

        $permission = Permission::findOrFail($permissionId);

        if ($permission->name === '*:*') {
            throw new RuntimeException('The wildcard permission cannot be managed through this endpoint.');
        }

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
        $role = $this->repository->findManageableById($roleId);

        $permission = Permission::findOrFail($permissionId);

        if ($permission->name === '*:*') {
            throw new RuntimeException('The wildcard permission cannot be managed through this endpoint.');
        }

        // Use Spatie's revokePermissionTo method
        $role->revokePermissionTo($permission);

        // Refresh to load updated permissions
        $role->refresh();

        return $role;
    }
}
