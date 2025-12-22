<?php

namespace App\Modules\CountryKpaUser\Service;

use App\Modules\CountryKpaUser\Domain\CountryKpaUser;
use App\Modules\CountryKpaUser\Repository\CountryKpaUserRepository;
use RuntimeException;

class CountryKpaUserService
{
    private CountryKpaUserRepository $repository;
    
    public function __construct(CountryKpaUserRepository $repository)
    {
        $this->repository = $repository;
    }
    
    /**
     * Assign a user to a CountryKpa
     *
     * @param int $countryKpaId
     * @param int $userId
     * @return CountryKpaUser
     */
    public function assignUserToCountryKpa(int $countryKpaId, int $userId): CountryKpaUser
    {
        $assignment = new CountryKpaUser([
            'country_kpa_id' => $countryKpaId,
            'user_id' => $userId
        ]);
        
        $this->repository->save($assignment);
        
        return $assignment;
    }
    
    /**
     * Update an assignment
     *
     * @param int $id
     * @param int $countryKpaId
     * @param int $userId
     * @return CountryKpaUser
     * @throws RuntimeException
     */
    public function updateAssignment(int $id, int $countryKpaId, int $userId): CountryKpaUser
    {
        $assignment = $this->repository->findById($id);
        
        $assignment->country_kpa_id = $countryKpaId;
        $assignment->user_id = $userId;
        
        $this->repository->save($assignment);
        
        return $assignment;
    }
    
    /**
     * Remove an assignment (physical delete)
     *
     * @param int $id
     * @return void
     * @throws RuntimeException
     */
    public function removeAssignment(int $id): void
    {
        $assignment = $this->repository->findById($id);
        $this->repository->delete($assignment);
    }
    
    /**
     * Get all users assigned to a specific CountryKpa
     *
     * @param int $countryKpaId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersByCountryKpa(int $countryKpaId)
    {
        return $this->repository->findByCountryKpaId($countryKpaId);
    }
    
    /**
     * Get all CountryKpas assigned to a specific User
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCountryKpasByUser(int $userId)
    {
        return $this->repository->findByUserId($userId);
    }
    
    /**
     * Get all assignments
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllAssignments()
    {
        return $this->repository->getAllWithRelations();
    }
    
    /**
     * Get assignment by ID with relationships
     *
     * @param int $id
     * @return CountryKpaUser
     * @throws RuntimeException
     */
    public function getAssignmentById(int $id): CountryKpaUser
    {
        $assignment = $this->repository->findById($id);
        $assignment->load(['countryKpa.country', 'countryKpa.kpa', 'user.role', 'user.userState']);
        
        return $assignment;
    }
}
