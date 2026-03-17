<?php

namespace App\Modules\Role\Repository;

use App\Repositories\AbstractRepository;
use App\Modules\Role\Domain\Role;

/**
 * @extends AbstractRepository<Role>
 */
class RoleRepository extends AbstractRepository
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    /**
     * Get roles that can be managed through the admin UI.
     */
    public function getManageableRoles()
    {
        return $this->model
            ->where('name', '!=', 'admin')
            ->orderBy('name')
            ->get();
    }

    /**
     * Search manageable roles by name.
     */
    public function searchManageableRoles(string $searchTerm)
    {
        return $this->model
            ->where('name', '!=', 'admin')
            ->whereRaw("LOWER(name) LIKE LOWER(?)", ['%' . trim($searchTerm) . '%'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Find a role that is allowed to be managed through the admin UI.
     *
     * @throws \RuntimeException
     */
    public function findManageableById(int $id): Role
    {
        $role = $this->findById($id);

        if ($role->name === 'admin') {
            throw new \RuntimeException("Role with ID {$id} not found");
        }

        return $role;
    }
}
