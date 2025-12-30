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
}
