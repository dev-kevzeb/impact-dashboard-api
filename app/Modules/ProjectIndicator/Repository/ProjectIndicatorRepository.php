<?php

namespace App\Modules\ProjectIndicator\Repository;

use App\Modules\ProjectIndicator\Domain\ProjectIndicator;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;

class ProjectIndicatorRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(ProjectIndicator $model)
    {
        parent::__construct($model);
    }

    public function findByProjectAndIndicator(int $indicatorId, int $projectId)
    {
        return $this->model->where('indicator_id', $indicatorId)
                           ->where('project_id', $projectId)
                           ->first();
    }

    public function delete(int $id)
    {
        $projectIndicador = $this->findById($id);
        if($projectIndicador) $projectIndicador->delete();
    }
}