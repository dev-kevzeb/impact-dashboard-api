<?php

namespace App\Modules\CountryJoinRequest\Repository;

use App\Modules\CountryJoinRequest\Domain\CountryJoinRequest;
use App\Repositories\AbstractRepository;

/**
 * @extends AbstractRepository<CountryJoinRequest>
 */
class CountryJoinRequestRepository extends AbstractRepository
{
    public function __construct(CountryJoinRequest $model)
    {
        parent::__construct($model);
    }

    public function findByCountryAndRequester(int $countryId, int $requesterUserRoleId): ?CountryJoinRequest
    {
        return $this->model
            ->where('country_id', $countryId)
            ->where('requester_user_role_id', $requesterUserRoleId)
            ->first();
    }

    public function findByIdWithRelations(int $id): CountryJoinRequest
    {
        $request = $this->baseQueryWithRelations()->find($id);

        if (!$request) {
            throw new \RuntimeException("CountryJoinRequest not found with id: {$id}");
        }

        return $request;
    }

    public function paginateByRequesterUserRoleIds(array $requesterUserRoleIds, int $perPage, ?string $status = null, ?int $countryId = null, ?string $search = null)
    {
        $query = $this->baseQueryWithRelations()->whereIn('requester_user_role_id', $requesterUserRoleIds);

        return $this->applyFilters($query, $status, $countryId, $search)->paginate($perPage);
    }

    public function paginateByCountryIds(array $countryIds, int $perPage, ?string $status = null, ?int $countryId = null, ?string $search = null)
    {
        $query = $this->baseQueryWithRelations()->whereIn('country_id', $countryIds);

        return $this->applyFilters($query, $status, $countryId, $search)->paginate($perPage);
    }

    public function paginateAll(int $perPage, ?string $status = null, ?int $countryId = null, ?string $search = null)
    {
        $query = $this->baseQueryWithRelations();

        return $this->applyFilters($query, $status, $countryId, $search)->paginate($perPage);
    }

    private function baseQueryWithRelations()
    {
        return $this->model
            ->with([
                'country',
                'requesterUserRole.user',
                'requesterUserRole.role',
            ])
            ->orderByDesc('id');
    }

    private function applyFilters($query, ?string $status, ?int $countryId, ?string $search)
    {
        if ($status) {
            $query->where('status', $status);
        }

        if ($countryId) {
            $query->where('country_id', $countryId);
        }

        if ($search) {
            $normalized = '%' . trim($search) . '%';
            $query->where(function ($q) use ($normalized) {
                $q->whereHas('country', function ($countryQuery) use ($normalized) {
                    $countryQuery->whereRaw('LOWER(name) LIKE LOWER(?)', [$normalized]);
                })->orWhereHas('requesterUserRole.user', function ($userQuery) use ($normalized) {
                    $userQuery->whereRaw('LOWER(name) LIKE LOWER(?)', [$normalized])
                        ->orWhereRaw('LOWER(email) LIKE LOWER(?)', [$normalized]);
                });
            });
        }

        return $query;
    }
}
