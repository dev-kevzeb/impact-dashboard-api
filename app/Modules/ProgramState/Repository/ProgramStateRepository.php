<?php

namespace App\Modules\ProgramState\Repository;

use App\Modules\ProgramState\Domain\ProgramState;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;
use RuntimeException;

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
}
