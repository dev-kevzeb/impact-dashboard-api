<?php

namespace App\Modules\IndicatorType\Repository;

use App\Modules\IndicatorType\Domain\IndicatorType;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;

class IndicatorTypeRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(IndicatorType $model)
    {
        parent::__construct($model);
    }

        public function existsByName(string $name): bool
    {
        return $this->model->whereRaw('LOWER(name) = LOWER(?)', [trim($name)])->exists();
    }
}