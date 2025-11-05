<?php 

namespace App\Modules\Country\Repository;

use App\Modules\Country\Domain\Country;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;
use RuntimeException;

class CountryRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(Country $model)
    {
        parent::__construct($model);
    }
}