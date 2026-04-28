<?php

namespace App\Modules\Beneficiary\Repository;

use App\Modules\Beneficiary\Domain\Beneficiary;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;
use RuntimeException;

class BeneficiaryRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(Beneficiary $model)
    {
        parent::__construct($model);
    }

    public function getPaginated(?string $search, int $perPage){
        $query = $this->model::query();
        if ($search) { $search = mb_strtolower($search);
            $query->whereRaw('LOWER(name) LIKE ?',['%' . $search . '%']);
        }

        return $query->paginate($perPage);
    }

    public function hasRelations(int $beneficiaryId): bool
    {
        return $this->model
            ->newQuery()
            ->whereKey($beneficiaryId)
            ->whereHas('projects')
            ->exists();
    }

    public function delete(int $id): void
    {
        $beneficiary = $this->findById($id);
        $beneficiary->delete();
    }
    
}
