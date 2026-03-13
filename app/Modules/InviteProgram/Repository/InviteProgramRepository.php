<?php

namespace App\Modules\InviteProgram\Repository;

use App\Modules\InviteProgram\Domain\InviteProgram;
use App\Repositories\AbstractRepository;

/**
 * @extends AbstractRepository<InviteProgram>
 */
class InviteProgramRepository extends AbstractRepository
{
    public function __construct(InviteProgram $model)
    {
        parent::__construct($model);
    }

    public function existsForPair(int $programCountryUserRoleId, int $invitedUserRoleId): bool
    {
        return $this->model
            ->where('program_country_user_role_id', $programCountryUserRoleId)
            ->where('invited_user_role_id', $invitedUserRoleId)
            ->exists();
    }

    public function paginateFiltered(int $perPage = 10, ?int $programCountryUserRoleId = null, ?int $invitedUserRoleId = null)
    {
        $query = $this->model->with([
            'programCountryUserRole.program.contact',
            'programCountryUserRole.program.programState',
            'programCountryUserRole.countryUserRole.country',
            'programCountryUserRole.countryUserRole.userRole.role',
            'invitedUserRole.user',
            'invitedUserRole.role',
        ]);

        if ($programCountryUserRoleId !== null) {
            $query->where('program_country_user_role_id', $programCountryUserRoleId);
        }

        if ($invitedUserRoleId !== null) {
            $query->where('invited_user_role_id', $invitedUserRoleId);
        }

        return $query->paginate($perPage);
    }

    public function findByIdWithRelations(int $id): InviteProgram
    {
        $invite = $this->model
            ->with([
                'programCountryUserRole.program.contact',
                'programCountryUserRole.program.programState',
                'programCountryUserRole.countryUserRole.country',
                'programCountryUserRole.countryUserRole.userRole.role',
                'invitedUserRole.user',
                'invitedUserRole.role',
            ])
            ->find($id);

        if (!$invite) {
            throw new \RuntimeException("InviteProgram not found with id: {$id}");
        }

        return $invite;
    }

    public function findFirstInviteByProgramAndInvitedRoleIds(int $programId, array $invitedUserRoleIds): ?InviteProgram
    {
        return $this->model
            ->with('programCountryUserRole.countryUserRole')
            ->whereIn('invited_user_role_id', $invitedUserRoleIds)
            ->whereHas('programCountryUserRole', function ($q) use ($programId) {
                $q->where('program_id', $programId);
            })
            ->first();
    }
}
