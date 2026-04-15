<?php

namespace App\Modules\ProjectInviteUser\Service;

use App\Modules\CountryUserRole\Domain\CountryUserRole;
use App\Modules\InviteProgram\Repository\InviteProgramRepository;
use App\Modules\ProgramCountryUserRole\Repository\ProgramCountryUserRoleRepository;
use App\Modules\Project\Domain\Project;
use App\Modules\ProjectInviteUser\Domain\ProjectInviteUser;
use RuntimeException;

class ProjectInviteUserService
{
    /** @var mixed */
    private $repository;

    public function __construct(
        private ProgramCountryUserRoleRepository $programCountryUserRoleRepository,
        private InviteProgramRepository $inviteProgramRepository,
    ) {
        $this->repository = app('App\\Modules\\ProjectInviteUser\\Repository\\ProjectInviteUserRepository');
    }

    public function attachProject(Project $project, CountryUserRole $countryUserRole): ProjectInviteUser
    {
        $existing = $this->repository->findByProjectAndCountryUserRole($project->id, $countryUserRole->id);
        if ($existing) {
            return $this->repository->findByIdWithRelations($existing->id);
        }

        $relation = ProjectInviteUser::at($project, $countryUserRole);
        $this->repository->save($relation);

        return $this->repository->findByIdWithRelations($relation->id);
    }

    public function hasFullProgramAccess(int $programId): bool
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('Not authenticated.');
        }

        if ($user->hasPermissionTo('*:*')) {
            return true;
        }

        $authUserRoleIds = $user->userRoles()->pluck('id')->toArray();

        $ownerAssignment = $this->programCountryUserRoleRepository
            ->findFirstOwnedAssignmentByProgramAndUserRoleIds($programId, $authUserRoleIds);

        return (bool) $ownerAssignment;
    }

    public function canViewProgramByCountry(int $programId): bool
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('Not authenticated.');
        }

        if (!$user->hasPermissionTo('programs:view_by_country')) {
            return false;
        }

        $countryIds = $user->userRoles()
            ->with('countries')
            ->get()
            ->flatMap(fn($userRole) => $userRole->countries->pluck('id'))
            ->unique()
            ->values()
            ->toArray();

        return $this->programCountryUserRoleRepository
            ->existsByProgramAndCountryIds($programId, $countryIds);
    }

    public function getVisibleProjectIdsForProgram(int $programId): array
    {
        if ($this->hasFullProgramAccess($programId)) {
            return [];
        }

        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('Not authenticated.');
        }

        $authUserRoleIds = $user->userRoles()->pluck('id')->toArray();

        $invite = $this->inviteProgramRepository
            ->findFirstInviteByProgramAndInvitedRoleIds($programId, $authUserRoleIds);

        if (!$invite) {
            throw new RuntimeException('You do not have access to this program context.');
        }

        $countryUserRole = $this->getCurrentCountryUserRole();

        return $this->repository->getProjectIdsByProgramAndCountryUserRole($programId, $countryUserRole->id);
    }

    public function canViewProject(Project $project): bool
    {
        $user = auth('api')->user();
        if (!$user) {
            return false;
        }

        if ($user->hasPermissionTo('*:*')) {
            return true;
        }

        $authUserRoleIds = $user->userRoles()->pluck('id')->toArray();

        $ownerAssignment = $this->programCountryUserRoleRepository
            ->findFirstOwnedAssignmentByProgramAndUserRoleIds((int) $project->program_id, $authUserRoleIds);

        if ($ownerAssignment) {
            return true;
        }

        if ($this->canViewProgramByCountry((int) $project->program_id)) {
            return true;
        }

        $countryUserRole = $this->tryGetCurrentCountryUserRole();
        if (!$countryUserRole) {
            return false;
        }

        return $this->repository->existsForProjectAndCountryUserRole($project->id, $countryUserRole->id);
    }

    public function canEditProject(Project $project): bool
    {
        $user = auth('api')->user();
        if (!$user) {
            return false;
        }

        if ($user->hasPermissionTo('*:*')) {
            return true;
        }

        $countryUserRole = $this->tryGetCurrentCountryUserRole();
        if (!$countryUserRole) {
            return false;
        }

        return $this->repository->existsForProjectAndCountryUserRole($project->id, $countryUserRole->id);
    }

    public function ensureCanViewProject(Project $project): void
    {
        if (!$this->canViewProject($project)) {
            throw new RuntimeException('You do not have access to this project.');
        }
    }

    public function ensureCanEditProject(Project $project): void
    {
        if (!$this->canEditProject($project)) {
            throw new RuntimeException('You are not allowed to edit this project.');
        }
    }

    public function applyProjectAccess(Project $project): Project
    {
        $project->setAttribute('can_edit', $this->canEditProject($project));

        return $project;
    }

    public function applyProjectAccessToPaginator($paginator)
    {
        $paginator->getCollection()->transform(function (Project $project) {
            return $this->applyProjectAccess($project);
        });

        return $paginator;
    }

    private function getCurrentCountryUserRole(): CountryUserRole
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('Not authenticated.');
        }

        return $user->getCountryUserRole();
    }

    private function tryGetCurrentCountryUserRole(): ?CountryUserRole
    {
        try {
            return $this->getCurrentCountryUserRole();
        } catch (RuntimeException) {
            return null;
        }
    }
}