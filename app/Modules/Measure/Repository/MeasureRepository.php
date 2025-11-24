<?php

namespace App\Modules\Measure\Repository;

use App\Modules\Measure\Domain\Measure;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;

class MeasureRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(Measure $measure)
    {
        parent::__construct($measure);
    }
}