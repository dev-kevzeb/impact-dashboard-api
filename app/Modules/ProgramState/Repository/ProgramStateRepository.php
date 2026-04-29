<?php

namespace App\Modules\ProgramState\Repository;

use App\Modules\ProgramState\Domain\ProgramState;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;

class ProgramStateRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(ProgramState $model)
    {
        parent::__construct($model);
    }

    public function getPaginated(?string $search, int $perPage = 10)
    {
        $query = $this->model->query();

        if (!empty($search)) {
            $query->whereRaw('LOWER(name) LIKE LOWER(?)', ['%' . trim($search) . '%']);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function hasRelations(int $programStateId): bool
    {
        return $this->model
            ->newQuery()
            ->whereKey($programStateId)
            ->whereHas('programs')
            ->exists();
    }

    public function delete(int $id): void
    {
        $programState = $this->findById($id);
        $programState->delete();
    }
}
