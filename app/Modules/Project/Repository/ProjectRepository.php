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
}