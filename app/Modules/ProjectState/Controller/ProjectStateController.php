<?php
namespace App\Modules\ProjectState\Controller;
use App\Http\Requests\ProjectStateRequest;
use App\Http\Resources\ProjectStateResource;
use App\Modules\ProjectState\Service\ProjectStateService;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Http\Requests\ProjectStateRequest;
use App\Http\Resources\ProjectStateResource;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class ProjectStateController extends Controller
{
    private ProjectStateService $projectStateService;

    public function __construct(ProjectStateService $projectStateService)
    {
        $this->projectStateService = $projectStateService;
    }

    public function index(): JsonResponse
    {
        try {
            $states = $this->projectStateService->getAllProjectStates();
            return ApiResponse::success(
                'Lista de estados obtenida exitosamente',
                200,
                [
                    'project_states' => ProjectStateResource::collection($states),
                    'total' => $states->count()
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $state = $this->projectStateService->getProjectStateById($id);
            return ApiResponse::success(
                'Estado del proyecto encontrado',
                200,
                new ProjectStateResource($state),
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Estado del proyecto');
        }
    }

    public function store(ProjectStateRequest $request)
    {
        try {
            $validated = $request->validated();
            $state = $this->projectStateService->createProjectState($validated['state']);
            return ApiResponse::success(
                'Estado del proyecto creado exitosamente',
                201,
                new ProjectStateResource($state)
            );
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['state' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function update(ProjectStateRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $state = $this->projectStateService->updateProjectState($id, $validated['state']);
            return ApiResponse::success(
                'Estado del proyecto actualizado exitosamente',
                200,
                new ProjectStateResource($state)
            );
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['state' => [$e->getMessage()]]);
            }
            if (str_contains($e->getMessage(), 'no encontrado')) {
                return ApiResponse::notFound('Estado del proyecto');
            }
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function search(): JsonResponse
    {
        try {
            $name = request()->query('name');
            
            if (!$name) {
                return ApiResponse::error('El parámetro name es requerido', 400);
            }
            
            $state = $this->projectStateService->findProjectStateByName($name);
            
            return ApiResponse::success(
                'Estado del proyecto encontrado',
                200,
                new ProjectStateResource($state)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Estado del proyecto');
        }
    }
}