<?php

namespace App\Modules\UserRole\Repository;

use App\Repositories\AbstractRepository;
use App\Modules\UserRole\Domain\UserRole;

/**
 * @extends AbstractRepository<UserRole>
 */
class UserRoleRepository extends AbstractRepository
{
    public function __construct(UserRole $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all roles assigned to a specific User
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByUserId(int $userId)
    {
        return $this->model
            ->with(['role', 'user'])
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * Get all users with a specific Role
     *
     * @param int $roleId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByRoleId(int $roleId)
    {
        return $this->model
            ->with(['user.userState', 'role'])
            ->where('role_id', $roleId)
            ->get();
    }

    /**
     * Check if user-role assignment already exists
     *
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    public function assignmentExists(int $userId, int $roleId): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->exists();
    }

    /**
     * Check if user-role assignment exists excluding specific ID
     *
     * @param int $userId
     * @param int $roleId
     * @param int $excludeId
     * @return bool
     */
    public function assignmentExistsExcluding(int $userId, int $roleId, int $excludeId): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->where('id', '!=', $excludeId)
            ->exists();
    }

    /**
     * Get all user-role assignments with relationships
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllWithRelations()
    {
        return $this->model
            ->with(['user.userState', 'role'])
            ->get();
    }
}
