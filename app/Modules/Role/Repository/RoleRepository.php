<?php

namespace App\Modules\Role\Repository;

use App\Repositories\AbstractRepository;
use App\Modules\Role\Domain\Role;

/**
 * @extends AbstractRepository<Role>
 */
class RoleRepository extends AbstractRepository
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }
}
