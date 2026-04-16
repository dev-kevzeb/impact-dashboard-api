<?php

namespace App\Modules\User\Repository;

use App\Repositories\AbstractRepository;
use App\Modules\User\Domain\User;

/**
 * @extends AbstractRepository<User>
 */
class UserRepository extends AbstractRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', strtolower(trim($email)))->first();
    }

    /**
     * Get paginated users with relationships
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateWithRelations(int $perPage = 10)
    {
        return $this->model
            ->with(['roles', 'userState'])
            ->paginate($perPage);
    }

    /**
     * Get paginated users excluding admin role
     * 
     * Admin manages the system but is not managed by the system.
     * Only returns users with roles: project-manager, country-manager, etc.
     * 
     * IMPORTANT: Only shows users with "active" or "inactive" states.
     * Users with "unverified" or "pending" states have their own endpoints.
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateManageableUsers(int $perPage = 10)
    {
        return $this->model
            ->with(['roles', 'userState', 'userRoles.countryUserRole.country', 'userRoles.countryUserRole.userRole.role'])
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->whereHas('userState', function ($query) {
                $query->whereIn('name', ['active', 'inactive']);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find user by ID with relationships
     *
     * @param int $id
     * @return User
     * @throws \RuntimeException
     */
    public function findByIdWithRelations(int $id): User
    {
        $user = $this->model
            ->with(['roles', 'userState', 'userRoles.countryUserRole.country', 'userRoles.countryUserRole.userRole.role'])
            ->find($id);

        if (!$user) {
            throw new \RuntimeException("User with ID {$id} not found");
        }

        return $user;
    }

    /**
     * Get paginated users with pending state
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPendingUsers(int $perPage = 10)
    {
        return $this->model
            ->with(['roles', 'userState', 'userRoles.countryUserRole.country', 'userRoles.countryUserRole.userRole.role'])
            ->whereHas('userState', function ($query) {
                $query->where('name', 'pending');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get paginated users with unverified state (email not verified)
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUnverifiedUsers(int $perPage = 10)
    {
        return $this->model
            ->with(['roles', 'userState', 'userRoles.countryUserRole.country', 'userRoles.countryUserRole.userRole.role'])
            ->whereHas('userState', function ($query) {
                $query->where('name', 'unverified');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get paginated admin users excluding the authenticated admin.
     *
     * @param int $excludedUserId
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateAdminsExcludingUser(int $excludedUserId, int $perPage = 10)
    {
        return $this->model
            ->with(['roles', 'userState'])
            ->where('id', '!=', $excludedUserId)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->whereHas('userState', function ($query) {
                $query->whereIn('name', ['active', 'inactive']);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find admin user by ID with relationships.
     *
     * @param int $id
     * @return User
     * @throws \RuntimeException
     */
    public function findAdminByIdWithRelations(int $id): User
    {
        $user = $this->model
            ->with(['roles', 'userState'])
            ->where('id', $id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->first();

        if (!$user) {
            throw new \RuntimeException("Admin user with ID {$id} not found");
        }

        return $user;
    }

    /**
     * Count active admin users.
     *
     * @return int
     */
    public function countActiveAdmins(): int
    {
        return $this->model
            ->whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->whereHas('userState', function ($query) {
                $query->where('name', 'active');
            })
            ->count();
    }

    /**
     * Delete user (hard delete)
     *
     * @param User $user
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
