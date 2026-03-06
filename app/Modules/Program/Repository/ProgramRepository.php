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
     * Get paginated programs with optional search by name
     */
    public function getPaginated(?string $search, int $perPage, string $sort = 'date_newest')
    {
        $query = $this->model
            ->with(['contact', 'programState', 'sdgs'])
            ->withCount('projects')
            ->when($search, function ($query) use ($search) {
                $query->whereRaw('LOWER(name) LIKE LOWER(?)', ['%' . trim($search) . '%']);
            });

        $this->applySort($query, $sort);

        return $query->paginate($perPage);
    }

    /**
     * Get paginated programs with public filters used by progress screens
     */
    public function getPublicPaginated(array $filters, ?string $search, int $perPage, string $sort = 'date_newest')
    {
        $countryId = data_get($filters, 'country.id');
        $kpaId = data_get($filters, 'kpa.id');
        $strategicOutputId = data_get($filters, 'strategic_output.id');
        $measureId = data_get($filters, 'measure.id');
        $programStateId = data_get($filters, 'program_state.id');

        $query = $this->model
            ->with(['contact', 'programState', 'sdgs'])
            ->withCount('projects')
            ->when($search, function ($query) use ($search) {
                $query->whereRaw('LOWER(name) LIKE LOWER(?)', ['%' . trim($search) . '%']);
            })
            ->when($countryId, function ($query) use ($countryId) {
                $query->whereHas('projects.indicators.measure.StrategicOutput.countryKpa', function ($subQuery) use ($countryId) {
                    $subQuery->where('id_country', $countryId);
                });
            })
            ->when($kpaId, function ($query) use ($kpaId) {
                $query->whereHas('projects.indicators.measure.StrategicOutput.countryKpa', function ($subQuery) use ($kpaId) {
                    $subQuery->where('id_kpa', $kpaId);
                });
            })
            ->when($strategicOutputId, function ($query) use ($strategicOutputId) {
                $query->whereHas('projects.indicators.measure', function ($subQuery) use ($strategicOutputId) {
                    $subQuery->where('strategic_output_id', $strategicOutputId);
                });
            })
            ->when($measureId, function ($query) use ($measureId) {
                $query->whereHas('projects.indicators.measure', function ($subQuery) use ($measureId) {
                    $subQuery->where('id', $measureId);
                });
            })
            ->when($programStateId, function ($query) use ($programStateId) {
                $query->where('program_state_id', $programStateId);
            });

        $this->applySort($query, $sort);

        return $query->paginate($perPage);
    }

    private function applySort($query, string $sort): void
    {
        switch ($sort) {
            case 'date_oldest':
                $query->orderBy('id', 'asc');
                break;
            case 'name_az':
                $query->orderBy('name', 'asc');
                break;
            case 'name_za':
                $query->orderBy('name', 'desc');
                break;
            case 'date_newest':
            default:
                $query->orderBy('id', 'desc');
                break;
        }
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
