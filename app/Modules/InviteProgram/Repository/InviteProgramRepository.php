<?php

namespace App\Modules\InviteProgram\Repository;

use App\Modules\InviteProgram\Domain\InviteProgram;
use App\Modules\UserRole\Domain\UserRole;
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

    public function paginateInviteCandidates(int $ownerUserRoleId, int $perPage = 10, ?string $search = null)
    {
        $query = UserRole::query()
            ->select([
                'user_role.id as id',
                'user.name as name',
                'user.email as email',
            ])
            ->join('user', 'user.id', '=', 'user_role.user_id')
            ->join('role', 'role.id', '=', 'user_role.role_id')
            ->leftJoin('user_state', 'user_state.id', '=', 'user.user_state_id')
            ->where('role.name', 'project-manager')
            ->where('user_role.id', '!=', $ownerUserRoleId)
            ->whereIn('user_state.name', ['active', 'inactive'])
            ->when($search, function ($q) use ($search) {
                $term = '%' . trim($search) . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->whereRaw('LOWER(user.name) LIKE LOWER(?)', [$term])
                        ->orWhereRaw('LOWER(user.email) LIKE LOWER(?)', [$term]);
                });
            })
            ->orderBy('user.name');

        return $query->paginate($perPage)->through(function ($row) {
            return (object) [
                'id' => (int) $row->id,
                'name' => $row->name,
                'email' => $row->email,
                'agency' => null,
            ];
        });
    }
}
