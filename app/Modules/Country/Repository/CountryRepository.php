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
            ->with('currency')
            ->withCount([
                'countryKpas',
                'countryKpas as strategic_outputs_count' => function ($q) {
                    $q->join('strategic_output', 'country_kpa.id', '=', 'strategic_output.id_ck');
                },
                'countryKpas as measures_count' => function ($q) {
                    $q->join('strategic_output', 'country_kpa.id', '=', 'strategic_output.id_ck')->join('measure', 'strategic_output.id', '=', 'measure.strategic_output_id');
                },
                'countryKpas as indicators_count' => function ($q) {
                    $q->join('strategic_output', 'country_kpa.id', '=', 'strategic_output.id_ck')->join('measure', 'strategic_output.id', '=', 'measure.strategic_output_id')->join('indicator', 'measure.id', '=', 'indicator.measure_id');
                }
            ])->orderBy('name', 'asc')->paginate($perPage);
    }

    public function getSimpleList(int $perPage = 100)
    {
        return $this->model
            ->select('id', 'name', 'currency_id')
            ->with('currency')
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    public function getPaginatedWithKpasNumber(?string $search, int $perPage = 10)
    {
        $query = $this->model::query();

        if ($search) $query->whereRaw('lower(name) LIKE lower(?)', ['%' . $search . '%']);

        return $query->withCount('kpas')->orderBy('name', 'asc')->paginate($perPage);
    }

    public function hasRelations(int $countryId): bool
    {
        return $this->model
            ->newQuery()
            ->whereKey($countryId)
            ->where(function ($query) {
                $query->whereHas('countryUserRoles')
                    ->orWhereHas('countryKpas', function ($countryKpaQuery) {
                        $countryKpaQuery->whereHas('strategicOutputs');
                    });
            })
            ->exists();
    }

    public function delete(int $id): void
    {
        $country = $this->findById($id);
        $country->delete();
    }
}
