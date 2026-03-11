<?php

namespace App\Modules\ProgramCountryUserRole\Service;

use App\Modules\ProgramCountryUserRole\Domain\ProgramCountryUserRole;
use App\Modules\ProgramCountryUserRole\Repository\ProgramCountryUserRoleRepository;

class ProgramCountryUserRoleService
{
    private ProgramCountryUserRoleRepository $repository;

    public function __construct(ProgramCountryUserRoleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new assignment between a Program and a CountryUserRole.
     *
     * @throws RuntimeException If the assignment already exists
     */
    public function createAssignment(int $programId, int $countryUserRoleId): ProgramCountryUserRole
    {
        $assignment = new ProgramCountryUserRole([
            'program_id'           => $programId,
            'country_user_role_id' => $countryUserRoleId,
        ]);

        $this->repository->save($assignment);

        return $this->repository->findByIdWithRelations($assignment->id);
    }

    /**
     * Get a paginated, optionally filtered list of assignments.
     */
    public function getAllAssignments(int $perPage = 10, ?int $programId = null, ?int $countryUserRoleId = null)
    {
        return $this->repository->paginateFiltered($perPage, $programId, $countryUserRoleId);
    }

    /**
     * Get a single assignment by ID with relationships.
     *
     * @throws RuntimeException If not found
     */
    public function getAssignmentById(int $id): ProgramCountryUserRole
    {
        return $this->repository->findByIdWithRelations($id);
    }

    /**
     * Remove an assignment (unlinks program from country user role; program stays in DB).
     *
     * @throws RuntimeException If not found
     */
    public function removeAssignment(int $id): void
    {
        $assignment = $this->repository->findById($id);
        $assignment->delete();
    }
}
