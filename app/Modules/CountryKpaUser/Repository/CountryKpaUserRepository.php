<?php

namespace App\Modules\CountryKpaUser\Repository;

use App\Repositories\AbstractRepository;
use App\Modules\CountryKpaUser\Domain\CountryKpaUser;

/**
 * @extends AbstractRepository<CountryKpaUser>
 */
class CountryKpaUserRepository extends AbstractRepository
{
    public function __construct(CountryKpaUser $model)
    {
        parent::__construct($model);
    }
    
    /**
     * Get all UserRoles assigned to a specific CountryKpa
     *
     * @param int $countryKpaId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByCountryKpaId(int $countryKpaId)
    {
        return $this->model
            ->with(['userRole.user.userState', 'userRole.role', 'countryKpa.country', 'countryKpa.kpa'])
            ->where('country_kpa_id', $countryKpaId)
            ->get();
    }
    
    /**
     * Get all CountryKpas assigned to a specific UserRole
     *
     * @param int $userRoleId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByUserRoleId(int $userRoleId)
    {
        return $this->model
            ->with(['countryKpa.country', 'countryKpa.kpa', 'userRole.user', 'userRole.role'])
            ->where('user_role_id', $userRoleId)
            ->get();
    }
    
    /**
     * Check if assignment already exists
     *
     * @param int $countryKpaId
     * @param int $userRoleId
     * @return bool
     */
    public function assignmentExists(int $countryKpaId, int $userRoleId): bool
    {
        return $this->model
            ->where('country_kpa_id', $countryKpaId)
            ->where('user_role_id', $userRoleId)
            ->exists();
    }
    
    /**
     * Check if assignment exists excluding specific ID
     *
     * @param int $countryKpaId
     * @param int $userRoleId
     * @param int $excludeId
     * @return bool
     */
    public function assignmentExistsExcluding(int $countryKpaId, int $userRoleId, int $excludeId): bool
    {
        return $this->model
            ->where('country_kpa_id', $countryKpaId)
            ->where('user_role_id', $userRoleId)
            ->where('id', '!=', $excludeId)
            ->exists();
    }
    
    /**
     * Get all assignments with relationships
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllWithRelations()
    {
        return $this->model
            ->with(['countryKpa.country', 'countryKpa.kpa', 'userRole.user.userState', 'userRole.role'])
            ->get();
    }
}
