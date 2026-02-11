<?php

namespace App\Modules\Project\Repository;

use App\Modules\Project\Domain\Project as P;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

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

    public function getPaginatedProjectsByProgramId(int $programId, ?string $search, int $perPage = 10){
        $query = $this->model->where('program_id', $programId)->with('projectState');
        if(!empty($search)) $query->whereRaw('lower(name) LIKE lower(?)', ['%'.trim($search).'%']);
        return $query->orderBy('name')->paginate($perPage);
    }

    public function getPaginated(?string $search, int $perPage = 10){
        $query = $this->model::query();
        if(!empty($search)) $query->whereRaw('lower(name) LIKE lower(?)', ['%'.trim($search).'%']);
        return $query->orderBy('name')->paginate($perPage);
    }
    public function getPaginatedByState(?int $projectStateId, ?string $search, int $perPage = 10) {
        $query = $this->model->query();
        if ($projectStateId !== null) $query->where('project_state_id', $projectStateId);
        if (!empty($search)) $query->whereRaw( 'lower(name) LIKE lower(?)', ['%' . trim($search) . '%']);
        return $query->orderBy('name')->paginate($perPage);
    }


    public function emptyPaginated(int $perPage)
    {
        return $this->model->whereRaw('1 = 0')->paginate($perPage);
    }

    public function paginateByIds(array $projectIds, ?int $projectStateId, ?string $search, int $perPage) {
        return $this->model->query()->whereIn('id', $projectIds)->when($projectStateId, fn ($q) =>$q->where('project_state_id', $projectStateId))
            ->when($search, fn ($q) =>$q->where('name', 'ILIKE', "%{$search}%"))
            ->paginate($perPage);
    }
}