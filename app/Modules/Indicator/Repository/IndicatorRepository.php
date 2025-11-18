<?php

namespace App\Modules\Indicator\Repository;

use App\Modules\Indicator\Domain\Indicator;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;

class IndicatorRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(Indicator $model)
    {
        parent::__construct($model);
    }
}

