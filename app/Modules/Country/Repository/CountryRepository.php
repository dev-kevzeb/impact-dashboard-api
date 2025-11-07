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

    /**
     * Buscar país por nombre (case-insensitive)
     */
    public function findByName(string $name): ?Country
    {
        return $this->model->whereRaw('LOWER(name) = LOWER(?)', [trim($name)])->first();
    }

    /**
     * Verificar si existe un país con ese nombre
     */
    public function existsByName(string $name): bool
    {
        return $this->model->whereRaw('LOWER(name) = LOWER(?)', [trim($name)])->exists();
    }

    /**
     * Guardar una entidad Country
     */
//     public function save(Country $country): void
//     {
//         $country->save();
//     }
}