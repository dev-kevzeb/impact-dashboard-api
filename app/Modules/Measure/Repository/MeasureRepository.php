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
}