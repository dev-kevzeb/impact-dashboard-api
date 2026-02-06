<?php

namespace App\Modules\Program\Repository;

use App\Repositories\AbstractRepository;
use App\Modules\Program\Domain\Program;

/**
 * Repository para Program
 * 
 * @extends AbstractRepository<Program>
 */
class ProgramRepository extends AbstractRepository
{
    /**
     * Constructor
     * 
     * @param Program $model Program model instance
     */
    public function __construct(Program $model)
    {
        parent::__construct($model);
    }

    /**
     * Get programs with all their relationships loaded
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllWithRelations()
    {
        return $this->model
            ->with([
                'contact',
                'programState',
                'sdgs'
            ])
            ->withCount('projects')
            ->get();
    }

    /**
     * Get paginated programs with all their relationships loaded
     * 
     * @param int $perPage Number of records per page
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginateWithRelations(int $perPage = 10)
    {
        return $this->model
            ->with([
                'contact',
                'programState',
                'sdgs'
            ])
            ->withCount('projects')
            ->paginate($perPage);
    }

    /**
     * Find program by ID with all its relationships
     * 
     * @param int $id
     * @return Program
     * @throws \RuntimeException If not found
     */
    public function findByIdWithRelations(int $id): Program
    {
        $program = $this->model
            ->with([
                'contact',
                'programState',
                'sdgs'
            ])
            ->withCount('projects')
            ->find($id);

        if (!$program) {
            throw new \RuntimeException("Program with ID {$id} not found");
        }

        return $program;
    }

    /**
     * Synchronize program SDGs
     * 
     * @param Program $program
     * @param array $sdgIds Array of SDG IDs
     * @return void
     */
    public function syncSdgs(Program $program, array $sdgIds): void
    {
        $program->sdgs()->sync($sdgIds);
    }

    /**
     * Get programs by state
     * 
     * @param int $programStateId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByProgramState(int $programStateId)
    {
        return $this->model
            ->where('program_state_id', $programStateId)
            ->with(['contact', 'programState', 'sdgs'])
            ->get();
    }
}
