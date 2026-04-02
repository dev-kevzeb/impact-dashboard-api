<?php

namespace App\Modules\Donor\Repository;

use App\Modules\Donor\Domain\Donor;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;
use RuntimeException;

class DonorRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(Donor $model)
    {
        parent::__construct($model);
    }

    public function findByName(string $name): ?Donor
    {
        $normalized = strtolower(trim($name));

        return $this->model
            ->whereRaw('LOWER(name) LIKE ?', ['%' . $normalized . '%'])
            ->first();
    }

    public function getExcluding(?string $search, int $perPage = 10, array $exclude = []){
        $query = $this->model::query();
        if(!empty($search)) $query->whereRaw('lower(name) LIKE lower(?)', ['%'.trim($search).'%']);
        $query->whereNotIn('id', $exclude);
        return $query->orderBy('name')->paginate($perPage);
    }

    public function hasRelations(int $donorId): bool
    {
        return $this->model
            ->newQuery()
            ->whereKey($donorId)
            ->whereHas('projectDonors')
            ->exists();
    }

    public function delete(int $id): void
    {
        $donor = $this->findById($id);
        $donor->delete();
    }
    
}