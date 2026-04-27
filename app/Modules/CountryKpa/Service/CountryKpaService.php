<?php

namespace App\Modules\CountryKpa\Service;

use App\Modules\CountryDashboardShare\Repository\CountryDashboardShareRepository;
use App\Modules\CountryKpa\Repository\CountryKpaRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use RuntimeException;

class CountryKpaService
{
    protected CountryKpaRepository $repo;
    protected CountryDashboardShareRepository $countryDashboardShareRepository;

    public function __construct(CountryKpaRepository $repo, CountryDashboardShareRepository $countryDashboardShareRepository)
    {
        $this->repo = $repo;
        $this->countryDashboardShareRepository = $countryDashboardShareRepository;
    }

    /**
     * Validate if the authenticated user has access to a specific country
     * 
     * @param int $countryId The country ID to check access for
     * @return bool True if user has access, false otherwise
     * @throws RuntimeException If no user is authenticated
     */
    public function userHasAccessToCountry(int $countryId): bool
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('No authenticated user found');
        }

        // Admin users have access to all countries
        if ($user->roles && $user->roles->contains(fn($role) => strtolower($role->name) === 'admin')) {
            return true;
        }

        // Non-admin users can only access their assigned country.
        // The authenticated User object does not expose a direct country_user_role relation,
        // so we resolve it from the loaded userRoles -> countryUserRole relationship or fallback to helper.
        if ($user->relationLoaded('userRoles')) {
            foreach ($user->userRoles as $userRole) {
                if ($userRole->relationLoaded('countryUserRole') && $userRole->countryUserRole) {
                    if ($userRole->countryUserRole->country_id === $countryId) {
                        return true;
                    }
                }
            }
        }

        if (method_exists($user, 'getCountryUserRole')) {
            try {
                $countryUserRole = $user->getCountryUserRole();
                return $countryUserRole->country_id === $countryId;
            } catch (RuntimeException $e) {
                // no assigned country
            }
        }

        return false;
    }

    public function userCanViewCountryDashboard(int $countryId): bool
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('No authenticated user found');
        }

        $isAdmin = $user->roles && $user->roles->contains(fn($role) => strtolower($role->name) === 'admin');
        if (!$isAdmin) {
            return $this->userHasAccessToCountry($countryId);
        }

        $userRoleIds = $user->userRoles()->pluck('id')->toArray();

        return $this->countryDashboardShareRepository->existsForCountryAndSharedUserRoleIds($countryId, $userRoleIds);
    }

    public function getAll(): array
    {
        return $this->repo->getAll();
    }

    public function getById(int $id): object
    {
        return $this->repo->getById($id);
    }

    public function getCountryKpasByCountryId(int $id, ?string $search, int $perPage): LengthAwarePaginator
    {
        return $this->repo->getCountryKpasByCountryId($id, $search, $perPage);
    }

    public function getByCountryAndKpa(int $CountryId, int $kpaId)
    {
        return $this->repo->getByCountryAndKpa($CountryId, $kpaId);
    }

    public function getByCountry(int $id)
    {
        return $this->repo->getByCountry($id);
    }


    public function create(array $data): object
    {
        return $this->repo->create($data);
    }

    public function delete(int $id): int
    {
        return $this->repo->delete($id);
    }

    public function update(int $id, array $data): object
    {
        return $this->repo->updateCountryKpa($id, $data);
    }

    public function attach(int $countryId, int $kpaId): object
    {
        return $this->repo->attachKpaToCountry($countryId, $kpaId);
    }

    public function detach(int $countryId, int $kpaId): int
    {
        return $this->repo->detachKpaFromCountry($countryId, $kpaId);
    }

    public function getKpasByCountryPaginated( int $countryId, ?string $search, int $perPage)
    {
        return $this->repo->getCountryKpasByCountryId($countryId, $search, $perPage);
    }
}

