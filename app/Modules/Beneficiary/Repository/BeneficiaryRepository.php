<?php

namespace App\Modules\Beneficiary\Repository;

use App\Modules\Beneficiary\Domain\Beneficiary;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;
use RuntimeException;

class BeneficiaryRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(Beneficiary $model)
    {
        parent::__construct($model);
    }
    
}
