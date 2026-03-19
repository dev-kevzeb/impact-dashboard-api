<?php

namespace App\Modules\InviteProgram\Service;

use App\Modules\InviteProgram\Domain\InviteProgram;
use App\Modules\InviteProgram\Repository\InviteProgramRepository;
use App\Modules\ProgramCountryUserRole\Domain\ProgramCountryUserRole;
use App\Modules\UserRole\Domain\UserRole;
use RuntimeException;

class InviteProgramService
{
    private InviteProgramRepository $repository;

    public function __construct(InviteProgramRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createInvite(int $programCountryUserRoleId, int $invitedUserRoleId): InviteProgram
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            throw new RuntimeException('Not authenticated.');
        }

        $authUserRoleIds = $authUser->userRoles()->pluck('id')->toArray();
        if (empty($authUserRoleIds)) {
            throw new RuntimeException('Authenticated user has no user role assigned.');
        }

        $isOwner = ProgramCountryUserRole::query()
            ->where('id', $programCountryUserRoleId)
            ->whereHas('countryUserRole', function ($q) use ($authUserRoleIds) {
                $q->whereIn('user_role_id', $authUserRoleIds);
            })
            ->exists();

        if (!$isOwner) {
            throw new RuntimeException('Only the program owner can invite project managers.');
        }

        if (in_array($invitedUserRoleId, $authUserRoleIds, true)) {
            throw new RuntimeException('You cannot invite yourself.');
        }

        $invitedUserRole = UserRole::with('role')->find($invitedUserRoleId);
        if (!$invitedUserRole) {
            throw new RuntimeException('The invited user role does not exist.');
        }

        if (!$invitedUserRole->role || $invitedUserRole->role->name !== 'project-manager') {
            throw new RuntimeException('Only project-manager roles can be invited.');
        }

        if ($this->repository->existsForPair($programCountryUserRoleId, $invitedUserRoleId)) {
            throw new RuntimeException('This project manager is already invited to this program.');
        }

        $invite = new InviteProgram([
            'program_country_user_role_id' => $programCountryUserRoleId,
            'invited_user_role_id' => $invitedUserRoleId,
        ]);

        $this->repository->save($invite);

        return $this->repository->findByIdWithRelations($invite->id);
    }

    public function getAllInvites(int $perPage = 10, ?int $programCountryUserRoleId = null, ?int $invitedUserRoleId = null)
    {
        return $this->repository->paginateFiltered($perPage, $programCountryUserRoleId, $invitedUserRoleId);
    }

    public function getInviteById(int $id): InviteProgram
    {
        return $this->repository->findByIdWithRelations($id);
    }

    public function getInviteCandidatesForOwner(int $programCountryUserRoleId, int $perPage = 10, ?string $search = null)
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            throw new RuntimeException('Not authenticated.');
        }

        $ownerAssignment = ProgramCountryUserRole::query()
            ->with('countryUserRole')
            ->find($programCountryUserRoleId);

        if (!$ownerAssignment || !$ownerAssignment->countryUserRole) {
            throw new RuntimeException('Program owner assignment not found.');
        }

        $authUserRoleIds = $authUser->userRoles()->pluck('id')->toArray();

        if (!in_array($ownerAssignment->countryUserRole->user_role_id, $authUserRoleIds, true)) {
            throw new RuntimeException('Only the program owner can invite project managers.');
        }

        return $this->repository->paginateInviteCandidates(
            (int) $ownerAssignment->countryUserRole->user_role_id,
            $perPage,
            $search
        );
    }

    public function removeInvite(int $id): void
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            throw new RuntimeException('Not authenticated.');
        }

        $authUserRoleIds = $authUser->userRoles()->pluck('id')->toArray();

        $invite = $this->repository->findByIdWithRelations($id);

        $isOwner = ProgramCountryUserRole::query()
            ->where('id', $invite->program_country_user_role_id)
            ->whereHas('countryUserRole', function ($q) use ($authUserRoleIds) {
                $q->whereIn('user_role_id', $authUserRoleIds);
            })
            ->exists();

        $isInvitedUser = in_array($invite->invited_user_role_id, $authUserRoleIds, true);

        if (!$isOwner && !$isInvitedUser) {
            throw new RuntimeException('You are not allowed to remove this invite.');
        }

        $invite->delete();
    }
}
