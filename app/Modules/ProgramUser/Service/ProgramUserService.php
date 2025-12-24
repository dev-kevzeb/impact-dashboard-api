<?php

namespace App\Modules\ProgramUser\Service;

use App\Modules\ProgramUser\Domain\ProgramUser;
use App\Modules\ProgramUser\Repository\ProgramUserRepository;
use RuntimeException;

class ProgramUserService
{
    private ProgramUserRepository $repository;

    public function __construct(ProgramUserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new assignment between Program and CountryKpaUser
     *
     * @param int $programId
     * @param int $countryKpaUserId
     * @return ProgramUser
     * @throws RuntimeException
     */
    public function createAssignment(int $programId, int $countryKpaUserId): ProgramUser
    {
        // Check if assignment already exists
        if ($this->repository->assignmentExists($programId, $countryKpaUserId)) {
            throw new RuntimeException('This CountryKpaUser is already assigned to this Program');
        }

        $assignment = new ProgramUser([
            'program_id' => $programId,
            'country_kpa_user_id' => $countryKpaUserId
        ]);

        $this->repository->save($assignment);

        return $assignment;
    }

    /**
     * Update an existing assignment
     *
     * @param int $id
     * @param int $programId
     * @param int $countryKpaUserId
     * @return ProgramUser
     * @throws RuntimeException
     */
    public function updateAssignment(int $id, int $programId, int $countryKpaUserId): ProgramUser
    {
        $assignment = $this->repository->findById($id);

        // Check if new combination already exists (excluding current assignment)
        $exists = ProgramUser::where('program_id', $programId)
            ->where('country_kpa_user_id', $countryKpaUserId)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            throw new RuntimeException('This CountryKpaUser is already assigned to this Program');
        }

        $assignment->program_id = $programId;
        $assignment->country_kpa_user_id = $countryKpaUserId;
        $this->repository->save($assignment);

        return $assignment;
    }

    /**
     * Get all assignments with pagination
     *
     * @param int $perPage
     * @param int|null $programId
     * @param int|null $countryKpaUserId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllAssignments(int $perPage = 10, ?int $programId = null, ?int $countryKpaUserId = null)
    {
        $query = ProgramUser::with(['program', 'countryKpaUser.countryKpa', 'countryKpaUser.userRole.user', 'countryKpaUser.userRole.role']);

        if ($programId) {
            $query->where('program_id', $programId);
        }

        if ($countryKpaUserId) {
            $query->where('country_kpa_user_id', $countryKpaUserId);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get assignment by ID
     *
     * @param int $id
     * @return ProgramUser
     * @throws RuntimeException
     */
    public function getAssignmentById(int $id): ProgramUser
    {
        $assignment = ProgramUser::with(['program', 'countryKpaUser.countryKpa', 'countryKpaUser.userRole.user', 'countryKpaUser.userRole.role'])
            ->find($id);

        if (!$assignment) {
            throw new RuntimeException("ProgramUser assignment not found with id: {$id}");
        }

        return $assignment;
    }

    /**
     * Remove an assignment
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
     * Get assignments by Program
     *
     * @param int $programId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAssignmentsByProgram(int $programId)
    {
        return $this->repository->findByProgram($programId);
    }

    /**
     * Get assignments by CountryKpaUser
     *
     * @param int $countryKpaUserId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAssignmentsByCountryKpaUser(int $countryKpaUserId)
    {
        return $this->repository->findByCountryKpaUser($countryKpaUserId);
    }
}
