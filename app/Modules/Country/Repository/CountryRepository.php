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

    public function getPaginated(?string $search, int $perPage = 10){
        $query = $this->model::query();

        if( $search ) $query->whereRaw('lower(name) LIKE lower(?)',['%' . $search . '%']);

        return $query->withCount('kpas')->paginate($perPage);
    }
    public function getPaginatedWithKpasNumber(?string $search, int $perPage = 10){
        $query = $this->model::query();

        if( $search ) $query->whereRaw('lower(name) LIKE lower(?)',['%' . $search . '%']);

        return $query->withCount('kpas')->paginate($perPage);
    }

}