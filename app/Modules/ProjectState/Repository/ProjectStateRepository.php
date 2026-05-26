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

    public function hasRelations(int $projectStateId): bool
    {
        return $this->model
            ->newQuery()
            ->whereKey($projectStateId)
            ->whereHas('projects')
            ->exists();
    }

    public function delete(int $id): void
    {
        $projectState = $this->findById($id);
        $projectState->delete();
    }
}