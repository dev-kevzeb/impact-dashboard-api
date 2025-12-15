<?php

namespace App\Modules\UserState\Repository;

use App\Modules\UserState\Domain\UserState;
use App\Repositories\AbstractRepository;

/**
 * @extends AbstractRepository<UserState>
 */
class UserStateRepository extends AbstractRepository
{
    public function __construct(UserState $model)
    {
        parent::__construct($model);
    }

    // Only custom methods here - base CRUD inherited from AbstractRepository
}
