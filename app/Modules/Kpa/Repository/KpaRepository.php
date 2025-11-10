<?php

namespace App\Modules\Kpa\Repository;

use App\Modules\Kpa\Domain\Kpa;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;
use RuntimeException;

class KpaRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(Kpa $model)
    {
        parent::__construct($model);
    }
    
}