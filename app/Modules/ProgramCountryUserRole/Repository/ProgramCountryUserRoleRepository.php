<?php

namespace App\Modules\ProgramCountryUserRole\Repository;

use App\Repositories\AbstractRepository;
use App\Modules\ProgramCountryUserRole\Domain\ProgramCountryUserRole;

/**
 * @extends AbstractRepository<ProgramCountryUserRole>
 */
class ProgramCountryUserRoleRepository extends AbstractRepository
{
    public function __construct(ProgramCountryUserRole $model)
    {
        parent::__construct($model);
    }

    /**
     * Return paginated assignments, optionally filtered by program_id and/or country_user_role_id.
     */
    public function paginateFiltered(int $perPage = 10, ?int $programId = null, ?int $countryUserRoleId = null)
    {
        $query = $this->model
            ->with([
                'program.contact',
                'program.programState',
                'program.sdgs',
                'countryUserRole.country',
                'countryUserRole.userRole.role',
            ]);

        if ($programId !== null) {
            $query->where('program_id', $programId);
        }

        if ($countryUserRoleId !== null) {
            $query->where('country_user_role_id', $countryUserRoleId);
        }

        return $query->paginate($perPage);
    }

    /**
     * Find an assignment by ID with relationships loaded.
     *
     * @throws \RuntimeException If not found
     */
    public function findByIdWithRelations(int $id): ProgramCountryUserRole
    {
        $assignment = $this->model
            ->with([
                'program.contact',
                'program.programState',
                'program.sdgs',
                'countryUserRole.country',
                'countryUserRole.userRole.role',
            ])
            ->find($id);

        if (!$assignment) {
            throw new \RuntimeException("ProgramCountryUserRole assignment not found with id: {$id}");
        }

        return $assignment;
    }

    public function findFirstOwnedAssignmentByProgramAndUserRoleIds(int $programId, array $userRoleIds): ?ProgramCountryUserRole
    {
        return $this->model
            ->with('countryUserRole')
            ->where('program_id', $programId)
            ->whereHas('countryUserRole', function ($q) use ($userRoleIds) {
                $q->whereIn('user_role_id', $userRoleIds);
            })
            ->first();
    }

    public function findFirstAssignmentByProgramId(int $programId): ?ProgramCountryUserRole
    {
        return $this->model
            ->with('countryUserRole')
            ->where('program_id', $programId)
            ->first();
    }

    public function existsByProgramAndCountryIds(int $programId, array $countryIds): bool
    {
        $countryIds = array_values(array_unique(array_map('intval', $countryIds)));

        if (empty($countryIds)) {
            return false;
        }

        return $this->model
            ->where('program_id', $programId)
            ->whereHas('countryUserRole', function ($q) use ($countryIds) {
                $q->whereIn('country_id', $countryIds);
            })
            ->exists();
    }
}
