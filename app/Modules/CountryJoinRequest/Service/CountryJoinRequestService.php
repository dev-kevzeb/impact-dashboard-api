<?php

namespace App\Modules\CountryJoinRequest\Service;

use App\Modules\CountryJoinRequest\Domain\CountryJoinRequest;
use App\Modules\CountryJoinRequest\Repository\CountryJoinRequestRepository;
use App\Modules\CountryUserRole\Domain\CountryUserRole;
use App\Modules\CountryUserRole\Repository\CountryUserRoleRepository;
use App\Modules\Role\Domain\Role;
use App\Modules\UserRole\Domain\UserRole;
use App\Notifications\CountryJoinRequestDecisionNotification;
use App\Notifications\CountryJoinRequestSubmittedNotification;
use RuntimeException;

class CountryJoinRequestService
{
    public function __construct(
        private CountryJoinRequestRepository $repository,
        private CountryUserRoleRepository $countryUserRoleRepository,
    ) {
    }

    public function listRequests(int $perPage = 10, ?string $status = null, ?int $countryId = null, ?string $search = null)
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('Not authenticated.');
        }

        if ($user->hasPermissionTo('*:*')) {
            return $this->repository->paginateAll($perPage, $status, $countryId, $search);
        }

        $countryManagerCountryIds = $this->getAuthenticatedCountryManagerCountryIds();
        if (!empty($countryManagerCountryIds)) {
            return $this->repository->paginateByCountryIds($countryManagerCountryIds, $perPage, $status, $countryId, $search);
        }

        $requesterUserRoleIds = $this->getAuthenticatedProjectManagerUserRoleIds();
        if (!empty($requesterUserRoleIds)) {
            return $this->repository->paginateByRequesterUserRoleIds($requesterUserRoleIds, $perPage, $status, $countryId, $search);
        }

        throw new RuntimeException('Only project-manager, country-manager or admin can access join requests.');
    }

    public function getRequestById(int $id): CountryJoinRequest
    {
        $request = $this->repository->findByIdWithRelations($id);
        $this->ensureAuthenticatedUserCanViewRequest($request);

        return $request;
    }

    public function createRequest(int $countryId): CountryJoinRequest
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('Not authenticated.');
        }

        $requesterUserRoleId = $this->getAuthenticatedProjectManagerUserRoleId();

        $countryUserRole = $this->countryUserRoleRepository->findByCountryAndUserRole($countryId, $requesterUserRoleId);
        if ($countryUserRole) {
            throw new RuntimeException('You already belong to this country.');
        }

        $country = \App\Modules\Country\Domain\Country::find($countryId);
        if (!$country) {
            throw new RuntimeException('The selected country does not exist.');
        }

        if (!(bool) $country->active) {
            throw new RuntimeException('Only active countries can receive join requests.');
        }

        $existing = $this->repository->findByCountryAndRequester($countryId, $requesterUserRoleId);
        if ($existing) {
            if ($existing->status === CountryJoinRequest::STATUS_PENDING) {
                throw new RuntimeException('A pending request already exists for this country.');
            }

            $existing->status = CountryJoinRequest::STATUS_PENDING;
            $this->repository->save($existing);

            $request = $this->repository->findByIdWithRelations((int) $existing->id);
            $this->notifyCountryManagersAboutSubmittedRequest($request);

            return $request;
        }

        $request = new CountryJoinRequest([
            'country_id' => $countryId,
            'requester_user_role_id' => $requesterUserRoleId,
            'status' => CountryJoinRequest::STATUS_PENDING,
        ]);

        $this->repository->save($request);
        $request = $this->repository->findByIdWithRelations((int) $request->id);

        $this->notifyCountryManagersAboutSubmittedRequest($request);

        return $request;
    }

    public function reviewRequest(int $id, string $action): CountryJoinRequest
    {
        $request = $this->repository->findByIdWithRelations($id);

        $this->resolveAuthenticatedCountryManagerRoleForCountry((int) $request->country_id);

        if ($action === 'approve') {
            $request->status = CountryJoinRequest::STATUS_APPROVED;
            if (!$this->countryUserRoleRepository->findByCountryAndUserRole((int) $request->country_id, (int) $request->requester_user_role_id)) {
                $assignment = new CountryUserRole([
                    'country_id' => (int) $request->country_id,
                    'user_role_id' => (int) $request->requester_user_role_id,
                ]);
                $this->countryUserRoleRepository->save($assignment);
            }
        } elseif ($action === 'revoke') {
            $request->status = CountryJoinRequest::STATUS_PENDING;
            $assignment = $this->countryUserRoleRepository->findByCountryAndUserRole((int) $request->country_id, (int) $request->requester_user_role_id);
            if ($assignment) {
                $assignment->delete();
            }
        } else {
            throw new RuntimeException('Unsupported action. Allowed values: approve, revoke.');
        }

        $this->repository->save($request);

        $request = $this->repository->findByIdWithRelations((int) $request->id);
        $this->notifyRequesterAboutDecision($request);

        return $request;
    }

    private function ensureAuthenticatedUserCanViewRequest(CountryJoinRequest $request): void
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('Not authenticated.');
        }

        if ($user->hasPermissionTo('*:*')) {
            return;
        }

        $requesterUserRoleIds = $this->getAuthenticatedProjectManagerUserRoleIds();
        if (in_array((int) $request->requester_user_role_id, $requesterUserRoleIds, true)) {
            return;
        }

        $countryManagerCountryIds = $this->getAuthenticatedCountryManagerCountryIds();
        if (in_array((int) $request->country_id, $countryManagerCountryIds, true)) {
            return;
        }

        throw new RuntimeException('You are not allowed to view this request.');
    }

    private function resolveAuthenticatedCountryManagerRoleForCountry(int $countryId): CountryUserRole
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('Not authenticated.');
        }

        $countryManagerRoleIds = $user->userRoles()
            ->whereHas('role', fn($q) => $q->where('name', 'country-manager'))
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        if (empty($countryManagerRoleIds)) {
            throw new RuntimeException('Only country-manager can review join requests.');
        }

        $countryUserRole = CountryUserRole::query()
            ->where('country_id', $countryId)
            ->whereIn('user_role_id', $countryManagerRoleIds)
            ->first();

        if (!$countryUserRole) {
            throw new RuntimeException('You can only review requests for your assigned country.');
        }

        return $countryUserRole;
    }

    private function getAuthenticatedProjectManagerUserRoleId(): int
    {
        $roleIds = $this->getAuthenticatedProjectManagerUserRoleIds();
        if (empty($roleIds)) {
            throw new RuntimeException('Only project-manager can create join requests.');
        }

        return (int) $roleIds[0];
    }

    private function getAuthenticatedProjectManagerUserRoleIds(): array
    {
        $user = auth('api')->user();
        if (!$user) {
            return [];
        }

        return $user->userRoles()
            ->whereHas('role', fn($q) => $q->where('name', 'project-manager'))
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->toArray();
    }

    private function getAuthenticatedCountryManagerCountryIds(): array
    {
        $user = auth('api')->user();
        if (!$user) {
            return [];
        }

        $countryManagerRoleIds = $user->userRoles()
            ->whereHas('role', fn($q) => $q->where('name', 'country-manager'))
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        if (empty($countryManagerRoleIds)) {
            return [];
        }

        return CountryUserRole::query()
            ->whereIn('user_role_id', $countryManagerRoleIds)
            ->pluck('country_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();
    }

    private function notifyCountryManagersAboutSubmittedRequest(CountryJoinRequest $request): void
    {
        $countryManagerRole = Role::query()->where('name', 'country-manager')->where('guard_name', 'api')->first();
        if (!$countryManagerRole) {
            return;
        }

        $countryManagerUserRoles = UserRole::query()
            ->with('user')
            ->where('role_id', (int) $countryManagerRole->id)
            ->whereHas('countryUserRole', fn($q) => $q->where('country_id', (int) $request->country_id))
            ->get();

        foreach ($countryManagerUserRoles as $userRole) {
            if ($userRole->user) {
                $userRole->user->notify(new CountryJoinRequestSubmittedNotification(
                    (string) ($request->country?->name ?? 'Unknown country'),
                    (string) ($request->requesterUserRole?->user?->name ?? 'Project Manager')
                ));
            }
        }
    }

    private function notifyRequesterAboutDecision(CountryJoinRequest $request): void
    {
        $requester = $request->requesterUserRole?->user;
        if (!$requester) {
            return;
        }

        $countryName = (string) ($request->country?->name ?? 'Unknown country');

        $requester->notify(new CountryJoinRequestDecisionNotification(
            (string) $request->status,
            $countryName,
        ));
    }
}
