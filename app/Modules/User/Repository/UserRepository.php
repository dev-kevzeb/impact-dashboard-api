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
            ->with(['role', 'userState'])
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
            ->with(['role', 'userState'])
            ->find($id);

        if (!$user) {
            throw new \RuntimeException("Usuario con ID {$id} no encontrado");
        }

        return $user;
    }
}
