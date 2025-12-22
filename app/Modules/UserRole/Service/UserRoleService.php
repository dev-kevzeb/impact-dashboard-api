<?php

namespace App\Modules\UserRole\Service;

use App\Modules\UserRole\Domain\UserRole;
use App\Modules\UserRole\Repository\UserRoleRepository;
use RuntimeException;

class UserRoleService
{
    private UserRoleRepository $repository;

    public function __construct(UserRoleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Assign a role to a user
     *
     * @param int $userId
     * @param int $roleId
     * @return UserRole
     * @throws RuntimeException
     */
    public function assignRoleToUser(int $userId, int $roleId): UserRole
    {
        // Check if assignment already exists
        if ($this->repository->assignmentExists($userId, $roleId)) {
            throw new RuntimeException('This user already has this role assigned');
        }

        $assignment = new UserRole([
            'user_id' => $userId,
            'role_id' => $roleId
        ]);

        $this->repository->save($assignment);

        return $assignment;
    }

    /**
     * Update an assignment
     *
     * @param int $id
     * @param int $userId
     * @param int $roleId
     * @return UserRole
     * @throws RuntimeException
     */
    public function updateAssignment(int $id, int $userId, int $roleId): UserRole
    {
        $assignment = $this->repository->findById($id);

        // Check if new combination would be duplicate (excluding current record)
        if ($this->repository->assignmentExistsExcluding($userId, $roleId, $id)) {
            throw new RuntimeException('This user already has this role assigned');
        }

        $assignment->user_id = $userId;
        $assignment->role_id = $roleId;

        $this->repository->save($assignment);

        return $assignment;
    }

    /**
     * Remove an assignment (physical delete)
     *
     * @param int $id
     * @return void
     * @throws RuntimeException
     */
    public function removeAssignment(int $id): void
    {
        $assignment = $this->repository->findById($id);
        $assignment->delete();
    }

    /**
     * Get all roles assigned to a specific User
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRolesByUser(int $userId)
    {
        return $this->repository->findByUserId($userId);
    }

    /**
     * Get all users with a specific Role
     *
     * @param int $roleId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersByRole(int $roleId)
    {
        return $this->repository->findByRoleId($roleId);
    }

    /**
     * Get all assignments
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllAssignments()
    {
        return $this->repository->getAllWithRelations();
    }

    /**
     * Get assignment by ID with relationships
     *
     * @param int $id
     * @return UserRole
     * @throws RuntimeException
     */
    public function getAssignmentById(int $id): UserRole
    {
        $assignment = $this->repository->findById($id);
        $assignment->load(['user.userState', 'role']);

        return $assignment;
    }
}
