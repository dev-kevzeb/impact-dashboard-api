<?php

namespace App\Modules\User\Service;

use App\Notifications\AccountApprovedNotification;
use App\Notifications\AccountRejectedNotification;
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

    /**
     * Get manageable users (excludes admin role)
     * 
     * Returns only users that can be managed through the admin panel.
     * Admin users are excluded as they manage the system itself.
     *
     * @param int $perPage Number of items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getManageableUsers(int $perPage = 10)
    {
        return $this->repository->paginateManageableUsers($perPage);
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
     * Get all users with unverified state (email not verified)
     *
     * @param int $perPage Number of items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUnverifiedUsers(int $perPage = 10)
    {
        return $this->repository->getUnverifiedUsers($perPage);
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

        // Security: Prevent managing admin users
        if ($user->roles()->where('name', 'admin')->exists()) {
            throw new RuntimeException('Cannot manage admin users through this endpoint.');
        }

        // Get active state
        $activeState = $this->userStateRepository->findBy('name', 'active');
        if (!$activeState) {
            throw new RuntimeException('Active state not found. Run UserStateSeeder.');
        }

        // Change state to active
        $user->user_state_id = $activeState->id;
        $this->repository->save($user);

        // Notify user that account is now approved and can login.
        $user->notify(new AccountApprovedNotification());

        return $user->fresh(['roles', 'userState']);
    }

    /**
     * Reject a user (delete registration request)
     * 
     * This permanently removes the user from database.
     * Accepts users in 'unverified' (email not verified) or 'pending' (waiting approval) states.
     * Use for unwanted/spam registrations or incomplete sign-ups.
     *
     * @param int $id User ID
     * @return bool
     * @throws RuntimeException
     */
    public function rejectUser(int $id): bool
    {
        $user = $this->repository->findById($id);

        // Validate user is in unverified or pending state
        if (!in_array($user->userState->name, ['unverified', 'pending'])) {
            throw new RuntimeException(
                "Cannot reject user. Only unverified/pending users can be rejected. Current state: {$user->userState->name}"
            );
        }

        // Security: Prevent managing admin users
        if ($user->roles()->where('name', 'admin')->exists()) {
            throw new RuntimeException('Cannot manage admin users through this endpoint.');
        }

        // Notify user before deleting the registration.
        $user->notify(new AccountRejectedNotification());

        // Delete user (hard delete)
        return $this->repository->delete($user);
    }

    /**
     * Change user state between active and inactive
     * 
     * Allows toggling user access without deleting the account.
     * Inactive users cannot login until reactivated.
     *
     * @param int $id User ID
     * @param string $newStateName State name ('active' or 'inactive')
     * @return User
     * @throws RuntimeException
     */
    public function changeUserState(int $id, string $newStateName): User
    {
        $user = $this->repository->findByIdWithRelations($id);

        // Security: Prevent managing admin users
        if ($user->roles()->where('name', 'admin')->exists()) {
            throw new RuntimeException('Cannot manage admin users through this endpoint.');
        }

        // Validate current state (only active/inactive can be changed)
        $currentState = $user->userState->name;
        if (!in_array($currentState, ['active', 'inactive'])) {
            throw new RuntimeException(
                "Cannot change state. User is in '{$currentState}' state. Only active/inactive users can be toggled."
            );
        }

        // Validate new state
        if (!in_array($newStateName, ['active', 'inactive'])) {
            throw new RuntimeException(
                "Invalid state '{$newStateName}'. Only 'active' or 'inactive' are allowed."
            );
        }

        // Prevent redundant state change
        if ($currentState === $newStateName) {
            throw new RuntimeException(
                "User is already in '{$newStateName}' state."
            );
        }

        // Get new state from database
        $newState = $this->userStateRepository->findBy('name', $newStateName);
        if (!$newState) {
            throw new RuntimeException("State '{$newStateName}' not found. Run UserStateSeeder.");
        }

        // Update user state
        $user->user_state_id = $newState->id;
        $this->repository->save($user);

        return $user->fresh(['roles', 'userState']);
    }
}
