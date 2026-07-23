<?php

namespace App\Modules\Measure\Repository;

use App\Modules\Measure\Domain\Measure;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;

class MeasureRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(Measure $measure)
    {
        parent::__construct($measure);
    }
    public function findByName(string $name): ?Measure
    {
        $normalized = strtolower(trim($name));

        return $this->model
            ->whereRaw('LOWER(name) LIKE ?', ['%' . $normalized . '%'])
            ->first();
    }

    public function getByStrategicOutput(int $strategicOutputId, ?string $search, int $perPage = 10)
    {
        $query = $this->model->where('strategic_output_id', $strategicOutputId);
        if(!empty($search)) $query->whereRaw('lower(name) LIKE lower(?)', ['%'.trim($search).'%']);

        return $query->orderBy('id')->paginate($perPage);
    }

    public function getAllByStrategicOutput(int $strategicOutputId, int $perPage = 10)
    {
        return $this->model->where('strategic_output_id', $strategicOutputId)->orderBy('id')->paginate($perPage);
    }

    public function getAllPaginatedByStrategicOutput(int $strategicOutputId, ?string $search, int $per_page)
    {
        $query = $this->model::query();

        $query->where('strategic_output_id', $strategicOutputId)->get();
        if( $search ) $query->whereRaw('lower(name) LIKE lower(?)',['%' . $search . '%']);

        return $query->orderBy('id')->paginate($per_page);
    }

    public function getByStrategicOutputIds(array $strategicOutputIds){
        $query = $this->model->whereIn('strategic_output_id', $strategicOutputIds);

        return $query->orderBy('id')->get();
    }

    public function getAllByStrategicOutputId(int $strategicOutputId)
    {
        return $this->model->where('strategic_output_id', $strategicOutputId)->orderBy('id')->get();
    }

    public function paginateByCountryKpaIds(array $countryKpaIds, ?string $search, int $perPage = 10)
    {
        $query = $this->model
            ->join('strategic_output', 'measure.strategic_output_id', '=', 'strategic_output.id')
            ->whereIn('strategic_output.id_ck', $countryKpaIds)
            ->select('measure.*')
            ->orderBy('measure.id');

        if (!empty($search)) {
            $query->whereRaw('LOWER(measure.name) LIKE LOWER(?)', ['%' . trim($search) . '%']);
        }

        return $query->paginate($perPage);
    }
}