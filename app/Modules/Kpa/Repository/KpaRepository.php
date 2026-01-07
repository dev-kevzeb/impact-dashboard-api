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

    public function getPaginated(?string $search, int $perPage = 10){
        $query = $this->model::query();

            if( $search ) $query->whereRaw('lower(name) LIKE lower(?)',['%' . $search . '%']);

        return $query->withCount([
                'countries as strategic_outputs_count' => function ($q) {
                $q->join(
                    'strategic_output',
                    'strategic_output.id_ck',
                    '=',
                    'country_kpa.id'
                );
            }
        ])->paginate($perPage);
    }
    
}