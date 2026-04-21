<?php

namespace App\Modules\Project\Repository;

use App\Modules\Project\Domain\Project as P;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProjectRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(P $model)
    {
        parent::__construct($model);
    }
    public function findByName(string $name): ? P
    {
        $normalized = strtolower(trim($name));

        return $this->model->whereRaw('LOWER(name) LIKE ?', ['%' . $normalized . '%'])->first();
    }

    public function findOneBy(string $field, mixed $value)
    {
        return $this->model->where($field, $value)->first();
    }

    public function findByNameAndProgramId(int $program_id, string $name): ?P
    {
        return $this->model->where('program_id', $program_id)->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($name) . '%'])->first();
    }

    public function getPaginatedProjectsByProgramId(int $programId, ?string $search, int $perPage = 10){
        $query = $this->model->where('program_id', $programId)->with('projectState');
        if(!empty($search)) $query->whereRaw('lower(name) LIKE lower(?)', ['%'.trim($search).'%']);
        return $query->orderBy('name')->paginate($perPage);
    }

    public function getDashboardProjectsPaginated(?string $search, int $perPage = 10)
    {
        return $this->buildDashboardProjectsQuery($search)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function getDashboardProjectsPaginatedByCountryIds(array $countryIds, ?string $search, int $perPage = 10)
    {
        $countryIds = array_values(array_unique(array_map('intval', $countryIds)));

        if (empty($countryIds)) {
            return $this->buildDashboardProjectsQuery($search)
                ->whereRaw('1 = 0')
                ->paginate($perPage);
        }

        return $this->buildDashboardProjectsQuery($search)
            ->whereHas('program.countryUserRoles', function ($query) use ($countryIds) {
                $query->whereIn('country_id', $countryIds);
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function getDashboardProjectsPaginatedByCountryUserRoleId(int $countryUserRoleId, ?string $search, int $perPage = 10)
    {
        return $this->buildDashboardProjectsQuery($search)
            ->whereHas('projectInviteUsers', function ($query) use ($countryUserRoleId) {
                $query->where('country_user_role_id', $countryUserRoleId);
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function getDashboardProjectsPaginatedByUserRoleContext(array $userRoleIds, ?int $countryUserRoleId, ?string $search, int $perPage = 10)
    {
        $userRoleIds = array_values(array_unique(array_map('intval', $userRoleIds)));

        if (empty($userRoleIds) && $countryUserRoleId === null) {
            return $this->buildDashboardProjectsQuery($search)
                ->whereRaw('1 = 0')
                ->paginate($perPage);
        }

        return $this->buildDashboardProjectsQuery($search)
            ->where(function ($query) use ($userRoleIds, $countryUserRoleId) {
                if (!empty($userRoleIds)) {
                    $query->whereHas('program.countryUserRoles', function ($ownerQuery) use ($userRoleIds) {
                        $ownerQuery->whereIn('user_role_id', $userRoleIds);
                    });
                }

                if ($countryUserRoleId !== null) {
                    $projectInviteConstraint = function ($inviteQuery) use ($countryUserRoleId) {
                        $inviteQuery->where('country_user_role_id', $countryUserRoleId);
                    };

                    if (empty($userRoleIds)) {
                        $query->whereHas('projectInviteUsers', $projectInviteConstraint);
                    } else {
                        $query->orWhereHas('projectInviteUsers', $projectInviteConstraint);
                    }
                }
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    private function buildDashboardProjectsQuery(?string $search): Builder
    {
        $query = $this->model->query()
            ->with([
                'program',
                'contact',
                'agencies',
                'indicators.measure.strategicOutput.countryKpa.country',
            ]);

        if (empty($search)) {
            return $query;
        }

        $searchTerm = '%' . mb_strtolower(trim($search)) . '%';

        return $query->where(function ($q) use ($searchTerm) {
            $q->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                ->orWhereRaw('LOWER(comments) LIKE ?', [$searchTerm])
                ->orWhereHas('program', function ($programQuery) use ($searchTerm) {
                    $programQuery->whereRaw('LOWER(name) LIKE ?', [$searchTerm]);
                })
                ->orWhereHas('contact', function ($contactQuery) use ($searchTerm) {
                    $contactQuery->whereRaw('LOWER(first_name) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', [$searchTerm]);
                })
                ->orWhereHas('agencies', function ($agencyQuery) use ($searchTerm) {
                    $agencyQuery->whereRaw('LOWER(name) LIKE ?', [$searchTerm]);
                })
                ->orWhereHas('indicators.measure', function ($measureQuery) use ($searchTerm) {
                    $measureQuery->whereRaw('LOWER(name) LIKE ?', [$searchTerm]);
                })
                ->orWhereHas('indicators.measure.strategicOutput.countryKpa.country', function ($countryQuery) use ($searchTerm) {
                    $countryQuery->whereRaw('LOWER(name) LIKE ?', [$searchTerm]);
                });
        });
    }

    public function getIdsByProgramId(int $programId): array
    {
        return $this->model
            ->where('program_id', $programId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();
    }

    public function getPaginated(?string $search, int $perPage = 10, string $sort = 'date_newest'){
        $query = $this->model::query();
        if(!empty($search)) $query->whereRaw('lower(name) LIKE lower(?)', ['%'.trim($search).'%']);
        $this->applySort($query, $sort);
        return $query->paginate($perPage);
    }

    public function getPaginatedForProgram(int $programId, ?string $search, int $perPage = 10, string $sort = 'date_newest') {
        $query = $this->model->query()->where('program_id', $programId);
        if (!empty($search)) $query->whereRaw('lower(name) LIKE lower(?)', ['%' . trim($search) . '%']);
        $this->applySort($query, $sort);
        return $query->paginate($perPage);
    }
    
    public function getPaginatedByState(?int $projectStateId, ?string $search, int $perPage = 10, string $sort = 'date_newest') {
        $query = $this->model->query();
        if ($projectStateId !== null) $query->where('project_state_id', $projectStateId);
        if (!empty($search)) $query->whereRaw( 'lower(name) LIKE lower(?)', ['%' . trim($search) . '%']);
        $this->applySort($query, $sort);
        return $query->paginate($perPage);
    }

    public function getPaginatedByStateForProgram(int $programId, ?int $projectStateId, ?string $search, int $perPage = 10, string $sort = 'date_newest') {
        $query = $this->model->query()->where('program_id', $programId);
        if ($projectStateId !== null) $query->where('project_state_id', $projectStateId);
        if (!empty($search)) $query->whereRaw('lower(name) LIKE lower(?)', ['%' . trim($search) . '%']);
        $this->applySort($query, $sort);
        return $query->paginate($perPage);
    }

    public function emptyPaginated(int $perPage)
    {
        return $this->model->whereRaw('1 = 0')->paginate($perPage);
    }

    public function paginateByIds(array $projectIds, ?int $projectStateId, ?string $search, int $perPage, string $sort = 'date_newest') {
        $query = $this->model->query()->with('projectState')->whereIn('id', $projectIds)
            ->when($projectStateId, fn ($q) =>$q->where('project_state_id', $projectStateId))
            ->when($search, fn ($q) =>$q->whereRaw('LOWER(name) LIKE LOWER(?)', ['%' . trim($search) . '%']));

        $this->applySort($query, $sort);

        return $query->paginate($perPage);
    }

    public function paginateByIdsForProgram(int $programId, array $projectIds, ?int $projectStateId, ?string $search, int $perPage, string $sort = 'date_newest') {
        $query = $this->model->query()->with('projectState')->where('program_id', $programId)->whereIn('id', $projectIds)
            ->when($projectStateId, fn ($q) =>$q->where('project_state_id', $projectStateId))
            ->when($search, fn ($q) =>$q->whereRaw('LOWER(name) LIKE LOWER(?)', ['%' . trim($search) . '%']));

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

    public function getByIds(array $projectIds) {
        return $this->model->query()->with('beneficiary')->with('agencies')->with('donors')->whereIn('id', $projectIds)->orderBy('name')->get();
    }

    public function getProgramWeightSum(int $programId, ?int $excludeProjectId = null): float
    {
        $query = $this->model->query()->where('program_id', $programId);

        if ($excludeProjectId !== null) {
            $query->where('id', '!=', $excludeProjectId);
        }

        return (float) $query->sum('weight');
    }
}
