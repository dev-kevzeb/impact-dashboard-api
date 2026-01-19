<?php

namespace App\Modules\User\Service;

use App\Modules\User\Domain\User;
use App\Modules\User\Repository\UserRepository;
use App\Modules\UserState\Repository\UserStateRepository;
use RuntimeException;

class UserService
{
    public UserRepository $repository;
    private UserStateRepository $userStateRepository;

    public function __construct(
        UserRepository $repository,
        UserStateRepository $userStateRepository
    ) {
        $this->repository = $repository;
        $this->userStateRepository = $userStateRepository;
    }

    public function createUser(string $name, string $email, string $password, int $userStateId): User
    {
        $userState = $this->userStateRepository->findById($userStateId);
        if (!$userState) {
            throw new RuntimeException("The user state with id {$userStateId} does not exist.");
        }

        $user = User::at($name, $email, $userState);
        $user->password = $password;
        $this->repository->save($user);

        return $user;
    }

    public function updateUser(int $id, string $name, string $email, int $userStateId, ?string $password = null): User
    {
        $user = $this->repository->findById($id);

        $userState = $this->userStateRepository->findById($userStateId);
        if (!$userState) {
            throw new RuntimeException("The user state with id {$userStateId} does not exist.");
        }

        $updated = User::at($name, $email, $userState);
        $user->name = $updated->name;
        $user->email = $updated->email;
        $user->user_state_id = $updated->user_state_id;

        if ($password !== null) {
            $user->password = $password;
        }

        $this->repository->save($user);

        return $user;
    }

    public function findUserByName(string $name): User
    {
        return $this->repository->findBy("name", $name);
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->repository->findByEmail($email);
    }

    public function getAllUsers(int $perPage = 10)
    {
        return $this->repository->paginateWithRelations($perPage);
    }

    public function getUserById(int $id): User
    {
        return $this->repository->findByIdWithRelations($id);
    }

    /**
     * Get all users with pending state (waiting for approval)
     *
     * @param int $perPage Number of items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPendingUsers(int $perPage = 10)
    {
        return $this->repository->getPendingUsers($perPage);
    }

    /**
     * Approve a pending user (change state from pending to active)
     * 
     * After approval, user can login with assigned role permissions.
     *
     * @param int $id User ID
     * @return User
     * @throws RuntimeException
     */
    public function approveUser(int $id): User
    {
        $user = $this->repository->findByIdWithRelations($id);

        // Validate user is in pending state
        if ($user->userState->name !== 'pending') {
            throw new RuntimeException(
                "User is not pending approval. Current state: {$user->userState->name}"
            );
        }

        // Get active state
        $activeState = $this->userStateRepository->findBy('name', 'active');
        if (!$activeState) {
            throw new RuntimeException('Active state not found. Run UserStateSeeder.');
        }

        // Change state to active
        $user->user_state_id = $activeState->id;
        $this->repository->save($user);

        return $user->fresh(['roles', 'userState']);
    }

    /**
     * Reject a pending user (delete registration request)
     * 
     * This permanently removes the user from database.
     * Use for unwanted/spam registrations.
     *
     * @param int $id User ID
     * @return bool
     * @throws RuntimeException
     */
    public function rejectUser(int $id): bool
    {
        $user = $this->repository->findById($id);

        // Validate user is in pending state
        if ($user->userState->name !== 'pending') {
            throw new RuntimeException(
                "Cannot reject user. Only pending users can be rejected. Current state: {$user->userState->name}"
            );
        }

        // Delete user (hard delete)
        return $this->repository->delete($user);
    }
}
