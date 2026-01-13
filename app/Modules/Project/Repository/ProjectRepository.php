<?php

namespace App\Modules\Project\Repository;

use App\Modules\Project\Domain\Project as P;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;

class ProjectRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(P $model)
    {
        parent::__construct($model);
    }
    public function findByName(string $name): ? P
    {
        $normalized = strtolower(trim($name));

        return $this->model->whereRaw('LOWER(name) LIKE ?', ['%' . $normalized . '%'])->first();
    }

    public function findOneBy(string $field, mixed $value)
    {
        return $this->model->where($field, $value)->first();
    }

    public function findByNameAndProgramId(int $program_id, string $name): ?P
    {
        return $this->model->where('program_id', $program_id)->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($name) . '%'])->first();
    }
}