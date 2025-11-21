<?php
namespace App\Modules\ProjectState\Controller;

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
                ProjectStateResource::collection($states)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function show($id): JsonResponse
    public function show($id): JsonResponse
    {
        try {
            $state = $this->projectStateService->getProjectStateById($id);
            return ApiResponse::success(
                'Estado del proyecto encontrado',
                200,
                new ProjectStateResource($state)
                new ProjectStateResource($state)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Estado del proyecto');
        }
    }

    public function store(ProjectStateRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $state = $this->projectStateService->createProjectState($validated['name']);
            
            return ApiResponse::created(
                'Estado del proyecto creado exitosamente',
                new ProjectStateResource($state)
                new ProjectStateResource($state)
            );
        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'ya existe')) {
                return ApiResponse::validationError(['state' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function update(ProjectStateRequest $request, $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $state = $this->projectStateService->updateProjectState($id, $validated['name']);
            
            return ApiResponse::success(
                'Estado del proyecto actualizado exitosamente',
                200,
                new ProjectStateResource($state)
                new ProjectStateResource($state)
            );
        } catch (RuntimeException $e) {
            // Si el mensaje indica que no se encontró, retornar 404
            if (str_contains($e->getMessage(), 'no existe')) {
                return ApiResponse::notFound('Estado del proyecto');
            }
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'ya existe')) {
                return ApiResponse::validationError(['state' => [$e->getMessage()]]);
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