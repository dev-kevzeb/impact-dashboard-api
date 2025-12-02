<?php

namespace App\Modules\Agency\Repository;

use App\Modules\Agency\Domain\Agency;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;
use RuntimeException;

/**
 * Repositorio para gestionar la persistencia de Agency
 * 
 * @extends AbstractRepository<Agency>
 */
class AgencyRepository extends AbstractRepository implements RepositoryInterface
{
    /**
     * Constructor
     * 
     * @param Agency $model
     */
    public function __construct(Agency $model)
    {
        parent::__construct($model);
    }

    public function findByName(string $name): ?Agency
    {
        $normalized = strtolower(trim($name));

        return $this->model
            ->whereRaw('LOWER(name) LIKE ?', ['%' . $normalized . '%'])
            ->first();
    }
}
