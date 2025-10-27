<?php

namespace App\Modules\ProgramState\Repository;

use App\Modules\ProgramState\Domain\ProgramState;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;
use RuntimeException;

class ProgramStateRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(ProgramState $model)
    {
        parent::__construct($model);
    }
}
