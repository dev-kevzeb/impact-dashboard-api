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

    public function existsByName(string $name): bool
    {
        
        $normalized = preg_replace('/\s+/', ' ', trim($name));
        $normalized = strtolower($normalized);

        return $this->model->whereRaw('LOWER(name) = ?', [$normalized])->exists();
    }

    public function getByCountryKpa(int $id)
    {
        return $this->model
            ->where('id_ck', $id)
            ->with('measures')
            ->get();
    }

}