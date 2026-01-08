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

    public function findByName(string $name): ? Indicator
    {
        $normalized = strtolower(trim($name));

        return $this->model
            ->whereRaw('LOWER(name) LIKE ?', ['%' . $normalized . '%'])
            ->first();
    }

    public function getByMeasure(int $measureId, ?string $search, int $perPage = 10, array $exclude = []){
        $query = $this->model->where('measure_id', $measureId);
        if(!empty($search)) $query->whereRaw('lower(name) LIKE lower(?)', ['%'.trim($search).'%']);
        $query->whereNotIn('id', $exclude);
        return $query->orderBy('name')->paginate($perPage);
    }
}

