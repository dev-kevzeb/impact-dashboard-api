<?php

namespace App\Modules\ProjectDonor\Repository;

use App\Modules\ProjectDonor\Domain\ProjectDonor;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;

class ProjectDonorRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(ProjectDonor $model)
    {
        parent::__construct($model);
    }

    public function findByProjectAndDonor(int $donorId, int $projectId)
    {
        return $this->model->where("donor_id", $donorId)->where('project_id', $projectId)->first();
    }

    public function delete(int $id)
    {
        $projectDonor = $this->findById($id);
        if($projectDonor) $projectDonor->delete();
    }

    public function deleteByProjectId(int $projectId)
    {
        $this->model->where('project_id', $projectId)->delete();
    }
}