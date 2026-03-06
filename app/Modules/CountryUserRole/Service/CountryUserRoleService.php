<?php

namespace App\Modules\CountryUserRole\Service;

use App\Modules\CountryUserRole\Domain\CountryUserRole;
use App\Modules\CountryUserRole\Repository\CountryUserRoleRepository;
use RuntimeException;

class CountryUserRoleService
{
    private CountryUserRoleRepository $repository;

    public function __construct(CountryUserRoleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new assignment between Country and UserRole
     *
     * @param int $countryId
     * @param int $userRoleId
     * @return CountryUserRole
     * @throws RuntimeException
     */
    public function createAssignment(int $countryId, int $userRoleId): CountryUserRole
    {
        // Check if assignment already exists
        if ($this->repository->assignmentExists($countryId, $userRoleId)) {
            throw new RuntimeException('This user role is already assigned to this country');
        }

        $assignment = new CountryUserRole([
            'country_id' => $countryId,
            'user_role_id' => $userRoleId
        ]);

        $this->repository->save($assignment);

        return $assignment;
    }

    /**
     * Remove an assignment
     *
     * @param int $countryId
     * @param int $userRoleId
     * @return void
     * @throws RuntimeException
     */
    public function removeAssignment(int $countryId, int $userRoleId): void
    {
        $assignment = $this->repository->findByCountryAndUserRole($countryId, $userRoleId);

        if (!$assignment) {
            throw new RuntimeException('Assignment not found');
        }

        $assignment->delete();
    }

    /**
     * Get all countries assigned to a user role
     *
     * @param int $userRoleId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCountriesForUserRole(int $userRoleId)
    {
        return $this->repository->getByUserRole($userRoleId);
    }

    /**
     * Get all user roles assigned to a country
     *
     * @param int $countryId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserRolesForCountry(int $countryId)
    {
        return $this->repository->getByCountry($countryId);
    }
}
