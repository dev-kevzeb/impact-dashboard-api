<?php

namespace App\Modules\Sdg\Repository;

use App\Modules\Sdg\Domain\Sdg;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;
use RuntimeException;


/**
 * Repositorio para la entidad SDG
 */
class SdgRepository extends AbstractRepository implements RepositoryInterface
{
    /**
     * Constructor
     * @param Sdg $model
     */
    public function __construct(Sdg $model)
    {
        parent::__construct($model);
    }
}
