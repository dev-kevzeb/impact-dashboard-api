<?php

namespace App\Modules\ProjectInviteUser\Repository;

use App\Modules\ProjectInviteUser\Domain\ProjectInviteUser;
use App\Repositories\AbstractRepository;

/**
 * @extends AbstractRepository<ProjectInviteUser>
 */
class ProjectInviteUserRepository extends AbstractRepository
{
    public function __construct(ProjectInviteUser $model)
    {
        parent::__construct($model);
    }

    public function findByProjectAndCountryUserRole(int $projectId, int $countryUserRoleId): ?ProjectInviteUser
    {
        return $this->model
            ->where('project_id', $projectId)
            ->where('country_user_role_id', $countryUserRoleId)
            ->first();
    }

    public function existsForProjectAndCountryUserRole(int $projectId, int $countryUserRoleId): bool
    {
        return $this->model
            ->where('project_id', $projectId)
            ->where('country_user_role_id', $countryUserRoleId)
            ->exists();
    }

    public function findByIdWithRelations(int $id): ProjectInviteUser
    {
        $relation = $this->model
            ->with([
                'project.program',
                'countryUserRole.country',
                'countryUserRole.userRole.role',
            ])
            ->find($id);

        if (!$relation) {
            throw new \RuntimeException("ProjectInviteUser not found with id: {$id}");
        }

        return $relation;
    }

    public function paginateFiltered(int $perPage = 10, ?int $projectId = null, ?int $countryUserRoleId = null)
    {
        $query = $this->model->with([
            'project.program',
            'countryUserRole.country',
            'countryUserRole.userRole.role',
        ]);

        if ($projectId !== null) {
            $query->where('project_id', $projectId);
        }

        if ($countryUserRoleId !== null) {
            $query->where('country_user_role_id', $countryUserRoleId);
        }

        return $query->paginate($perPage);
    }

    public function getProjectIdsByProgramAndCountryUserRole(int $programId, int $countryUserRoleId): array
    {
        return $this->model
            ->join('project', 'project.id', '=', 'project_invite_user.project_id')
            ->where('project.program_id', $programId)
            ->where('project_invite_user.country_user_role_id', $countryUserRoleId)
            ->distinct()
            ->pluck('project_invite_user.project_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function countProjectsByProgramAndCountryUserRole(int $programId, int $countryUserRoleId): int
    {
        return (int) $this->model
            ->join('project', 'project.id', '=', 'project_invite_user.project_id')
            ->where('project.program_id', $programId)
            ->where('project_invite_user.country_user_role_id', $countryUserRoleId)
            ->count('project_invite_user.id');
    }
}