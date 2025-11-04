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
        $normalized = mb_convert_case(preg_replace('/\s+/', ' ', trim($name)), MB_CASE_TITLE, "UTF-8");
        return $this->model->whereRaw('LOWER(name) = LOWER(?)', [$normalized])->first();
    }

    public function existsByName(string $name): bool
    {
        $normalized = mb_convert_case(preg_replace('/\s+/', ' ', trim($name)), MB_CASE_TITLE, "UTF-8");
        return $this->model->whereRaw('LOWER(name) = LOWER(?)', [$normalized])->exists();
    }
}