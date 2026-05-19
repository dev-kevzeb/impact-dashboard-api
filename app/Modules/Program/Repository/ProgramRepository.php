<?php

namespace App\Modules\Program\Repository;

use App\Repositories\AbstractRepository;
use App\Modules\Program\Domain\Program;
use Illuminate\Support\Facades\DB;

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
                'sdgs',
                'countryUserRoles.country',
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
                'sdgs',
                'countryUserRoles.country',
            ])
            ->withCount('projects')
            ->paginate($perPage);
    }

    public function paginateAccessibleByUserRoleIds(array $userRoleIds, int $perPage = 10, ?string $search = null)
    {
        $userRoleIds = array_values(array_unique(array_map('intval', $userRoleIds)));
        if (empty($userRoleIds)) {
            return $this->model->whereRaw('1 = 0')->paginate($perPage);
        }

        $idsSql = implode(',', $userRoleIds);

        $accessSubquery = DB::table('program_country_user_role as pcur')
            ->leftJoin('country_user_role as cur', 'cur.id', '=', 'pcur.country_user_role_id')
            ->leftJoin('invite_program as ip', 'ip.program_country_user_role_id', '=', 'pcur.id')
            ->where(function ($q) use ($userRoleIds) {
                $q->whereIn('cur.user_role_id', $userRoleIds)
                    ->orWhereIn('ip.invited_user_role_id', $userRoleIds);
            })
            ->select('pcur.program_id')
            ->selectRaw("MAX(CASE WHEN cur.user_role_id IN ({$idsSql}) THEN 1 ELSE 0 END) as can_edit")
            ->groupBy('pcur.program_id');

        return $this->model
            ->joinSub($accessSubquery, 'access_programs', function ($join) {
                $join->on('program.id', '=', 'access_programs.program_id');
            })
            ->with([
                'contact',
                'programState',
                'sdgs',
                'countryUserRoles.country',
            ])
            ->withCount('projects')
            ->when($search, function ($query) use ($search) {
                $query->whereRaw('LOWER(program.name) LIKE LOWER(?)', ['%' . trim($search) . '%']);
            })
            ->select('program.*')
            ->selectRaw('CAST(access_programs.can_edit AS integer) as can_edit')
            ->orderBy('program.id', 'desc')
            ->paginate($perPage);
    }

    public function paginateByCountryIds(array $countryIds, int $perPage = 10, ?string $search = null, array $editableUserRoleIds = [])
    {
        $countryIds = array_values(array_unique(array_map('intval', $countryIds)));
        if (empty($countryIds)) {
            return $this->model->whereRaw('1 = 0')->paginate($perPage);
        }

        $editableUserRoleIds = array_values(array_unique(array_map('intval', $editableUserRoleIds)));
        $editableIdsSql = !empty($editableUserRoleIds) ? implode(',', $editableUserRoleIds) : null;

        $accessSubquery = DB::table('program_country_user_role as pcur')
            ->leftJoin('country_user_role as cur', 'cur.id', '=', 'pcur.country_user_role_id')
            ->whereIn('cur.country_id', $countryIds)
            ->select('pcur.program_id')
            ->selectRaw(
                $editableIdsSql
                    ? "MAX(CASE WHEN cur.user_role_id IN ({$editableIdsSql}) THEN 1 ELSE 0 END) as can_edit"
                    : '0 as can_edit'
            )
            ->groupBy('pcur.program_id');

        return $this->model
            ->joinSub($accessSubquery, 'access_programs', function ($join) {
                $join->on('program.id', '=', 'access_programs.program_id');
            })
            ->with([
                'contact',
                'programState',
                'sdgs',
                'countryUserRoles.country',
            ])
            ->withCount('projects')
            ->when($search, function ($query) use ($search) {
                $query->whereRaw('LOWER(program.name) LIKE LOWER(?)', ['%' . trim($search) . '%']);
            })
            ->select('program.*')
            ->selectRaw('CAST(access_programs.can_edit AS integer) as can_edit')
            ->orderBy('program.id', 'desc')
            ->paginate($perPage);
    }

    public function isEditableByUserRoleIds(int $programId, array $userRoleIds): bool
    {
        $userRoleIds = array_values(array_unique(array_map('intval', $userRoleIds)));
        if (empty($userRoleIds)) {
            return false;
        }

        return DB::table('program_country_user_role as pcur')
            ->join('country_user_role as cur', 'cur.id', '=', 'pcur.country_user_role_id')
            ->where('pcur.program_id', $programId)
            ->whereIn('cur.user_role_id', $userRoleIds)
            ->exists();
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
                'sdgs',
                'countryUserRoles.country',
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
