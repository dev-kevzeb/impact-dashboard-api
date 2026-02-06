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

        return $query->orderBy('name')->paginate($perPage);
    }

    public function getAllByStrategicOutput(int $strategicOutputId)
    {
        return $this->model->where('strategic_output_id', $strategicOutputId)->get();
    }

    public function getByStrategicOutputIds(array $strategicOutputIds){
        $query = $this->model->whereIn('strategic_output_id', $strategicOutputIds);

        return $query->orderBy('name')->get();
    }

}