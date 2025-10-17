<?php

namespace App\Modules\Donor\Repository;

use App\Modules\Donor\Domain\Donor;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;
use RuntimeException;

class DonorRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(Donor $model)
    {
        parent::__construct($model);
    }
    
}