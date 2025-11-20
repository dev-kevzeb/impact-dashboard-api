<?php

namespace App\Modules\Currency\Repository;

use App\Modules\Currency\Domain\Currency;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;
use RuntimeException;

class CurrencyRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(Currency $model)
    {
        parent::__construct($model);
    }

    public function exists(string $field, $value): bool
    {
        return $this->model->where($field, $value)->exists();
    }

}
