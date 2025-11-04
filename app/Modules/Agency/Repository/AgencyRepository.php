<?php

namespace App\Modules\Agency\Repository;

use App\Repositories\AbstractRepository;
use App\Modules\Agency\Domain\Agency;

/**
 * Repositorio para gestionar la persistencia de Agency
 * 
 * @extends AbstractRepository<Agency>
 */
class AgencyRepository extends AbstractRepository
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
}
