<?php

namespace App\Modules\UserState\Service;

use App\Modules\UserState\Domain\UserState;
use App\Modules\UserState\Repository\UserStateRepository;
use RuntimeException;

class UserStateService
{
    private UserStateRepository $repository;

    public function __construct(UserStateRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new UserState
     *
     * @param string $name
     * @return UserState
     * @throws RuntimeException
     */
    public function createUserState(string $name): UserState
    {
        $userState = UserState::at($name);
        $this->repository->save($userState);

        return $userState;
    }

    /**
     * Update an existing UserState
     *
     * @param int $id
     * @param string $name
     * @return UserState
     * @throws RuntimeException
     */
    public function updateUserState(int $id, string $name): UserState
    {
        $userState = $this->repository->findById($id);

        $updated = UserState::at($name);
        $userState->name = $updated->name;
        $this->repository->save($userState);

        return $userState;
    }

    /**
     * Find UserState by name (exact match)
     *
     * @param string $name
     * @return UserState
     * @throws RuntimeException
     */
    public function findUserStateByName(string $name): UserState
    {
        return $this->repository->findBy('name', $name);
    }

    /**
     * Get all UserStates
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllUserStates()
    {
        return $this->repository->getAll();
    }

    /**
     * Find UserState by ID
     *
     * @param int $id
     * @return UserState
     * @throws RuntimeException
     */
    public function findUserStateById(int $id): UserState
    {
        return $this->repository->findById($id);
    }

    /**
     * Search UserStates by name (LIKE pattern for partial matching)
     *
     * @param string $searchTerm
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchUserStates(string $searchTerm)
    {
        $model = UserState::query();
        return $model->whereRaw("LOWER(name) LIKE LOWER(?)", ['%' . trim($searchTerm) . '%'])
            ->get();
    }
}
