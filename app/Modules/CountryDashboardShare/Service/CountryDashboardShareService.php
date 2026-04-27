<?php

namespace App\Modules\CountryDashboardShare\Service;

use App\Notifications\CountryDashboardAccessNotification;
use App\Modules\CountryDashboardShare\Domain\CountryDashboardShare;
use App\Modules\CountryDashboardShare\Repository\CountryDashboardShareRepository;
use App\Modules\CountryUserRole\Repository\CountryUserRoleRepository;
use App\Modules\User\Repository\UserRepository;
use App\Modules\UserRole\Domain\UserRole;
use RuntimeException;

class CountryDashboardShareService
{
    public function __construct(
        private CountryDashboardShareRepository $repository,
        private CountryUserRoleRepository $countryUserRoleRepository,
        private UserRepository $userRepository,
    ) {
    }

    public function createShare(int $countryId, int $sharedUserRoleId): CountryDashboardShare
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('Not authenticated.');
        }

        $ownerCountryUserRole = $user->getCountryUserRole();

        if ((int) $ownerCountryUserRole->country_id !== $countryId) {
            throw new RuntimeException('You can only share dashboards for your assigned country.');
        }

        $ownerRoleName = strtolower((string) optional(optional($ownerCountryUserRole->userRole)->role)->name);
        if ($ownerRoleName !== 'country-manager') {
            throw new RuntimeException('Only country-manager can share country dashboards.');
        }

        $sharedUserRole = UserRole::with(['role', 'user'])->find($sharedUserRoleId);
        if (!$sharedUserRole || !$sharedUserRole->role) {
            throw new RuntimeException('The shared user role does not exist.');
        }

        if (strtolower($sharedUserRole->role->name) !== 'admin') {
            throw new RuntimeException('Country dashboard can only be shared with admin users.');
        }

        if ($this->repository->findByCountryAndSharedUserRole($countryId, $sharedUserRoleId)) {
            throw new RuntimeException('This admin is already approved for the selected country dashboard.');
        }

        $share = new CountryDashboardShare([
            'country_id' => $countryId,
            'owner_country_user_role_id' => (int) $ownerCountryUserRole->id,
            'shared_user_role_id' => $sharedUserRoleId,
        ]);

        $this->repository->save($share);

        $share = $this->repository->findByIdWithRelations((int) $share->id);

        $sharedAdmin = $share->sharedUserRole?->user;
        if ($sharedAdmin) {
            $sharedAdmin->notify(new CountryDashboardAccessNotification(
                true,
                (string) ($share->country?->name ?? 'Unknown country'),
                (string) ($user->name ?? 'Country Manager'),
            ));
        }

        return $share;
    }

    public function deleteShare(int $id): void
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('Not authenticated.');
        }

        $share = $this->repository->findByIdWithRelations($id);

        $sharedAdmin = $share->sharedUserRole?->user;
        $countryName = (string) ($share->country?->name ?? 'Unknown country');
        $ownerName = (string) ($share->ownerCountryUserRole?->userRole?->user?->name ?? $user->name ?? 'Country Manager');

        if ($user->hasPermissionTo('*:*')) {
            $share->delete();
            if ($sharedAdmin) {
                $sharedAdmin->notify(new CountryDashboardAccessNotification(false, $countryName, $ownerName));
            }
            return;
        }

        $ownerCountryUserRole = $user->getCountryUserRole();
        if ((int) $share->owner_country_user_role_id !== (int) $ownerCountryUserRole->id) {
            throw new RuntimeException('You are not allowed to revoke this share.');
        }

        $share->delete();
        if ($sharedAdmin) {
            $sharedAdmin->notify(new CountryDashboardAccessNotification(false, $countryName, $ownerName));
        }
    }

    public function getSharesCreatedByAuthenticatedCountryManager(int $perPage = 10)
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('Not authenticated.');
        }

        $ownerCountryUserRole = $user->getCountryUserRole();

        return $this->repository->paginateByOwnerCountryUserRole((int) $ownerCountryUserRole->id, $perPage);
    }

    public function getVisibleSharesForAuthenticatedAdmin(int $perPage = 10)
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('Not authenticated.');
        }

        $userRoleIds = $user->userRoles()->pluck('id')->toArray();

        if (empty($userRoleIds)) {
            throw new RuntimeException('Authenticated user has no roles assigned.');
        }

        return $this->repository->paginateBySharedUserRoleIds($userRoleIds, $perPage);
    }

    public function canAdminViewSharedCountry(int $countryId): bool
    {
        $user = auth('api')->user();
        if (!$user) {
            return false;
        }

        $userRoleIds = $user->userRoles()->pluck('id')->toArray();

        return $this->repository->existsForCountryAndSharedUserRoleIds($countryId, $userRoleIds);
    }

    public function getShareableAdminsForAuthenticatedCountryManager(int $perPage = 10)
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('Not authenticated.');
        }

        if (!$user->hasRole('country-manager')) {
            throw new RuntimeException('Only country-manager can list admin share candidates.');
        }

        $user->getCountryUserRole();

        return $this->userRepository->paginateAdminsExcludingUser((int) $user->id, $perPage);
    }
}
