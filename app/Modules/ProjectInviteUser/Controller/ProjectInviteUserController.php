<?php

namespace App\Modules\ProjectInviteUser\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectInviteUserRequest;
use App\Http\Resources\ProjectInviteUserResource;
use App\Http\Responses\ApiResponse;
use App\Modules\ProjectInviteUser\Service\ProjectInviteUserService;
use App\Modules\Project\Repository\ProjectRepository;
use App\Modules\CountryUserRole\Repository\CountryUserRoleRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ProjectInviteUserController extends Controller
{
    /** @var mixed */
    private $repository;

    public function __construct(
        private ProjectInviteUserService $service,
    )
    {
        $this->repository = app('App\\Modules\\ProjectInviteUser\\Repository\\ProjectInviteUserRepository');
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $projectId = $request->get('project_id') ? (int) $request->get('project_id') : null;
        $countryUserRoleId = $request->get('country_user_role_id') ? (int) $request->get('country_user_role_id') : null;

        $relations = $this->repository->paginateFiltered($perPage, $projectId, $countryUserRoleId);

        return ApiResponse::success('Project invite user list successfully obtained', 200, [
            'relations' => ProjectInviteUserResource::collection($relations),
            'total' => $relations->total(),
            'per_page' => $relations->perPage(),
            'current_page' => $relations->currentPage(),
            'last_page' => $relations->lastPage(),
        ]);
    }

    public function store(ProjectInviteUserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $project = app(ProjectRepository::class)->findById((int) $validated['project_id']);
            if (!$project) {
                throw new RuntimeException('The project does not exist.');
            }

            $countryUserRole = app(CountryUserRoleRepository::class)->findById((int) $validated['country_user_role_id']);
            if (!$countryUserRole) {
                throw new RuntimeException('The country user role does not exist.');
            }

            $relation = $this->service->attachProject($project, $countryUserRole);

            return ApiResponse::created('Project invite user created successfully', new ProjectInviteUserResource($relation));
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $relation = $this->repository->findByIdWithRelations($id);

            return ApiResponse::success('Project invite user found', 200, new ProjectInviteUserResource($relation));
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('ProjectInviteUser');
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $relation = $this->repository->findByIdWithRelations($id);
            $relation->delete();

            return ApiResponse::success('Project invite user deleted successfully', 200);
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('ProjectInviteUser');
        }
    }
}