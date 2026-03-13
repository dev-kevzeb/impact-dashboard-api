<?php
namespace App\Modules\StrategicOutput\Repository;

use App\Modules\StrategicOutput\Domain\StrategicOutput;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;

class StrategicOutputRepository extends AbstractRepository implements RepositoryInterface
{
    
    public function __construct(StrategicOutput $model)
    {
        parent::__construct($model);
    }

    public function findByName(string $name): ?StrategicOutput
    {
        $normalized = strtolower(trim($name));

        return $this->model
            ->whereRaw('LOWER(name) LIKE ?', ['%' . $normalized . '%'])
            ->first();
    }

    public function existsByNameAndCountryKpa(string $name, int $id_ck): bool
    {
        $normalized = strtolower(preg_replace('/\s+/', ' ', trim($name)));

        return $this->model
            ->whereRaw('LOWER(name) = ?', [$normalized])
            ->where('id_ck', $id_ck)
            ->exists();
    }

    public function getByCountryKpa(int $id, int $perPage = 10)
    {
        return $this->model
            ->where('id_ck', $id)
            ->with('measures')
            ->paginate($perPage);
    }

    public function paginateByCountryKpaIds(array $countryKpaIds, ?string $search, int $perPage =10){
        $query = $this->model->whereIn('id_ck', $countryKpaIds);

        if(!empty($search)) $query->whereRaw('lower(name) LIKE lower(?)', ['%'.trim($search).'%']);

        return $query->orderBy('name')->paginate($perPage);
    }

    public function getByCountryKpaIds(array $countryKpaIds){
        $query = $this->model->whereIn('id_ck', $countryKpaIds);

        return $query->orderBy('name')->get();
    }

    public function belongsToCountry(int $strategicOutputId, int $countryId): bool
    {
        return $this->model
            ->where('id', $strategicOutputId)
            ->whereHas('countryKpa', function ($q) use ($countryId) {
                $q->where('id_country', $countryId);
            })
            ->exists();
    }
}