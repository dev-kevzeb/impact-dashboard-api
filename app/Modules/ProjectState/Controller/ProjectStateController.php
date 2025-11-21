<?php
namespace App\Modules\ProjectState\Controller;
use App\Modules\ProjectState\Service\ProjectStateService;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\ProjectStateResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class ProjectStateController extends Controller
{
    private ProjectStateService $projectStateService;

    public function __construct(ProjectStateService $projectStateService)
    {
        $this->projectStateService = $projectStateService;
    }

    // Metodos a futuro
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
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
        
    }

    public function show($id): JsonResponse
    {
        try {
            $state = $this->projectStateService->getProjectStateById($id);
            return ApiResponse::success(
                'Estado encontrado',
                200,
                new ProjectStateResource($state)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Estado del proyecto');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'state' => 'required|string|max:255',
        ]);

        try {
            $state = $this->projectStateService->createProjectState($request->input('state'));
            return ApiResponse::created(
                'Estado del proyecto creado exitosamente',
                new ProjectStateResource($state)
            );
        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'ya existe')) {
                return ApiResponse::validationError(['state' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'state' => 'required|string|max:255',
        ]);

        try {
            $state = $this->projectStateService->updateProjectState($id, $request->input('state'));
            return ApiResponse::success(
                'Estado del proyecto actualizado exitosamente',
                200,
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
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

}