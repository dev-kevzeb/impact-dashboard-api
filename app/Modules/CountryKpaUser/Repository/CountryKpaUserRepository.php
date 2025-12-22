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
     * Get all users assigned to a specific CountryKpa
     *
     * @param int $countryKpaId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByCountryKpaId(int $countryKpaId)
    {
        return $this->model
            ->with(['user.role', 'user.userState', 'countryKpa.country', 'countryKpa.kpa'])
            ->where('country_kpa_id', $countryKpaId)
            ->get();
    }
    
    /**
     * Get all CountryKpas assigned to a specific User
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByUserId(int $userId)
    {
        return $this->model
            ->with(['countryKpa.country', 'countryKpa.kpa', 'user.role', 'user.userState'])
            ->where('user_id', $userId)
            ->get();
    }
    
    /**
     * Check if assignment already exists
     *
     * @param int $countryKpaId
     * @param int $userId
     * @return bool
     */
    public function assignmentExists(int $countryKpaId, int $userId): bool
    {
        return $this->model
            ->where('country_kpa_id', $countryKpaId)
            ->where('user_id', $userId)
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
            ->with(['countryKpa.country', 'countryKpa.kpa', 'user.role', 'user.userState'])
            ->get();
    }
}
