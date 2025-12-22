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
     * Assign a UserRole to a CountryKpa
     *
     * @param int $countryKpaId
     * @param int $userRoleId
     * @return CountryKpaUser
     * @throws RuntimeException
     */
    public function assignUserRoleToCountryKpa(int $countryKpaId, int $userRoleId): CountryKpaUser
    {
        // Check if assignment already exists
        if ($this->repository->assignmentExists($countryKpaId, $userRoleId)) {
            throw new RuntimeException('This user role is already assigned to this CountryKpa');
        }

        $assignment = new CountryKpaUser([
            'country_kpa_id' => $countryKpaId,
            'user_role_id' => $userRoleId
        ]);
        
        $this->repository->save($assignment);
        
        return $assignment;
    }
    
    /**
     * Update an assignment
     *
     * @param int $id
     * @param int $countryKpaId
     * @param int $userRoleId
     * @return CountryKpaUser
     * @throws RuntimeException
     */
    public function updateAssignment(int $id, int $countryKpaId, int $userRoleId): CountryKpaUser
    {
        $assignment = $this->repository->findById($id);
        
        // Check if new combination would be duplicate (excluding current record)
        if ($this->repository->assignmentExistsExcluding($countryKpaId, $userRoleId, $id)) {
            throw new RuntimeException('This user role is already assigned to this CountryKpa');
        }

        $assignment->country_kpa_id = $countryKpaId;
        $assignment->user_role_id = $userRoleId;
        
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
        $assignment->delete();
    }
    
    /**
     * Get all UserRoles assigned to a specific CountryKpa
     *
     * @param int $countryKpaId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserRolesByCountryKpa(int $countryKpaId)
    {
        return $this->repository->findByCountryKpaId($countryKpaId);
    }
    
    /**
     * Get all CountryKpas assigned to a specific UserRole
     *
     * @param int $userRoleId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCountryKpasByUserRole(int $userRoleId)
    {
        return $this->repository->findByUserRoleId($userRoleId);
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
        $assignment->load(['countryKpa.country', 'countryKpa.kpa', 'userRole.user.userState', 'userRole.role']);
        
        return $assignment;
    }
}
