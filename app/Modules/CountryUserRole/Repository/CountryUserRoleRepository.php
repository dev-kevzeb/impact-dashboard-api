<?php

namespace App\Modules\CountryUserRole\Repository;

use App\Modules\CountryUserRole\Domain\CountryUserRole;
use App\Repositories\AbstractRepository;

/**
 * @extends AbstractRepository<CountryUserRole>
 */
class CountryUserRoleRepository extends AbstractRepository
{
    public function __construct(CountryUserRole $model)
    {
        parent::__construct($model);
    }

    /**
     * Check if a country-user_role assignment already exists
     *
     * @param int $countryId
     * @param int $userRoleId
     * @return bool
     */
    public function assignmentExists(int $countryId, int $userRoleId): bool
    {
        return $this->model
            ->where('country_id', $countryId)
            ->where('user_role_id', $userRoleId)
            ->exists();
    }

    /**
     * Find assignment by country and user_role
     *
     * @param int $countryId
     * @param int $userRoleId
     * @return CountryUserRole|null
     */
    public function findByCountryAndUserRole(int $countryId, int $userRoleId): ?CountryUserRole
    {
        return $this->model
            ->where('country_id', $countryId)
            ->where('user_role_id', $userRoleId)
            ->first();
    }

    /**
     * Get all assignments for a specific user_role
     *
     * @param int $userRoleId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByUserRole(int $userRoleId)
    {
        return $this->model
            ->where('user_role_id', $userRoleId)
            ->with('country')
            ->get();
    }

    /**
     * Get all assignments for a specific country
     *
     * @param int $countryId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByCountry(int $countryId)
    {
        return $this->model
            ->where('country_id', $countryId)
            ->with('userRole.user', 'userRole.role')
            ->get();
    }
}
