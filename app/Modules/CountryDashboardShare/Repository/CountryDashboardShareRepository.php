<?php

namespace App\Modules\CountryDashboardShare\Repository;

use App\Modules\CountryDashboardShare\Domain\CountryDashboardShare;
use App\Repositories\AbstractRepository;

/**
 * @extends AbstractRepository<CountryDashboardShare>
 */
class CountryDashboardShareRepository extends AbstractRepository
{
    public function __construct(CountryDashboardShare $model)
    {
        parent::__construct($model);
    }

    public function findByCountryAndSharedUserRole(int $countryId, int $sharedUserRoleId): ?CountryDashboardShare
    {
        return $this->model
            ->where('country_id', $countryId)
            ->where('shared_user_role_id', $sharedUserRoleId)
            ->first();
    }

    public function findByIdWithRelations(int $id): CountryDashboardShare
    {
        $share = $this->model
            ->with([
                'country',
                'ownerCountryUserRole.country',
                'ownerCountryUserRole.userRole.user',
                'ownerCountryUserRole.userRole.role',
                'sharedUserRole.user',
                'sharedUserRole.role',
            ])
            ->find($id);

        if (!$share) {
            throw new \RuntimeException("CountryDashboardShare not found with id: {$id}");
        }

        return $share;
    }

    public function paginateByOwnerCountryUserRole(int $ownerCountryUserRoleId, int $perPage = 10)
    {
        return $this->model
            ->with([
                'country',
                'sharedUserRole.user',
                'sharedUserRole.role',
            ])
            ->where('owner_country_user_role_id', $ownerCountryUserRoleId)
            ->paginate($perPage);
    }

    public function paginateBySharedUserRoleIds(array $sharedUserRoleIds, int $perPage = 10)
    {
        $sharedUserRoleIds = array_values(array_unique(array_map('intval', $sharedUserRoleIds)));

        return $this->model
            ->with([
                'country',
                'ownerCountryUserRole.country',
                'ownerCountryUserRole.userRole.user',
                'ownerCountryUserRole.userRole.role',
            ])
            ->whereIn('shared_user_role_id', $sharedUserRoleIds)
            ->paginate($perPage);
    }

    public function existsForCountryAndSharedUserRoleIds(int $countryId, array $sharedUserRoleIds): bool
    {
        $sharedUserRoleIds = array_values(array_unique(array_map('intval', $sharedUserRoleIds)));

        if (empty($sharedUserRoleIds)) {
            return false;
        }

        return $this->model
            ->where('country_id', $countryId)
            ->whereIn('shared_user_role_id', $sharedUserRoleIds)
            ->exists();
    }
}
