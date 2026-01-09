<?php
namespace App\Modules\ProjectState\Repository;
use App\Modules\ProjectState\Domain\ProjectState as PS;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;

class ProjectStateRepository extends AbstractRepository implements RepositoryInterface {
    public function __construct(PS $model)
    {
        parent::__construct($model);
    }

    public function getPaginated(?string $search, int $perPage){
        $query = $this->model::query();
        if ($search) { $search = mb_strtolower($search);
            $query->whereRaw('LOWER(state) LIKE ?',['%' . $search . '%']);
        }

    return $query->paginate($perPage);
    }
}