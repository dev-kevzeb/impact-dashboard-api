<?php

namespace App\Modules\UserRole\Repository;

use App\Repositories\AbstractRepository;
use App\Modules\UserRole\Domain\UserRole;

/**
 * @extends AbstractRepository<UserRole>
 */
class UserRoleRepository extends AbstractRepository
{
    public function __construct(UserRole $model)
    {
        parent::__construct($model);
    }
}
