<?php

namespace App\Modules\ProgramUser\Repository;

use App\Repositories\AbstractRepository;
use App\Modules\ProgramUser\Domain\ProgramUser;

/**
 * @extends AbstractRepository<ProgramUser>
 */
class ProgramUserRepository extends AbstractRepository
{
    public function __construct(ProgramUser $model)
    {
        parent::__construct($model);
    }

    /**
     * Find assignments by Program ID
     *
     * @param int $programId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByProgram(int $programId)
    {
        return $this->model->where('program_id', $programId)->get();
    }

    /**
     * Find assignments by CountryKpaUser ID
     *
     * @param int $countryKpaUserId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByCountryKpaUser(int $countryKpaUserId)
    {
        return $this->model->where('country_kpa_user_id', $countryKpaUserId)->get();
    }

    /**
     * Check if assignment exists
     *
     * @param int $programId
     * @param int $countryKpaUserId
     * @return bool
     */
    public function assignmentExists(int $programId, int $countryKpaUserId): bool
    {
        return $this->model
            ->where('program_id', $programId)
            ->where('country_kpa_user_id', $countryKpaUserId)
            ->exists();
    }
}
