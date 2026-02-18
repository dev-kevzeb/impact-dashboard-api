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

    public function findByName(string $name): ?Country
    {
        return $this->model->whereRaw('LOWER(name) = LOWER(?)', [trim($name)])->first();
    }

    public function existsByName(string $name): bool
    {
        return $this->model->whereRaw('LOWER(name) = LOWER(?)', [trim($name)])->exists();
    }

    public function getPaginated(?string $search, int $perPage = 10)
    {
        $query = $this->model->query();
        if ($search) $query->whereRaw('LOWER(name) LIKE LOWER(?)', ['%' . $search . '%']);
        
        return $query
            ->withCount([
                'countryKpas',
                'countryKpas as strategic_outputs_count' => function ($q) {$q->join('strategic_output', 'country_kpa.id', '=', 'strategic_output.id_ck');},
                'countryKpas as measures_count' => function ($q) {$q->join('strategic_output', 'country_kpa.id', '=', 'strategic_output.id_ck')->join('measure', 'strategic_output.id', '=', 'measure.strategic_output_id');},
                'countryKpas as indicators_count' => function ($q) { $q->join('strategic_output', 'country_kpa.id', '=', 'strategic_output.id_ck') ->join('measure', 'strategic_output.id', '=', 'measure.strategic_output_id') ->join('indicator', 'measure.id', '=', 'indicator.measure_id'); }
            ])->paginate($perPage);
    }

    public function getPaginatedWithKpasNumber(?string $search, int $perPage = 10){
        $query = $this->model::query();

        if( $search ) $query->whereRaw('lower(name) LIKE lower(?)',['%' . $search . '%']);

        return $query->withCount('kpas')->paginate($perPage);
    }

}