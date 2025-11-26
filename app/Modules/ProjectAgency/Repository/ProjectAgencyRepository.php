<?php

namespace App\Modules\ProjectAgency\Repository;

use App\Modules\ProjectAgency\Domain\ProjectAgency;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;

class ProjectAgencyRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(ProjectAgency $model)
    {
        parent::__construct($model);
    }

    public function findByProjectAndAgency(int $agencyId, int $projectId): ?ProjectAgency
    {
        return $this->model->where('agency_id', $agencyId)
                           ->where('project_id', $projectId)
                           ->first();
    }

    public function delete (int $id): void
    {
        $projectAgency = $this->findById($id);
        if ($projectAgency) $projectAgency->delete();
    }
}