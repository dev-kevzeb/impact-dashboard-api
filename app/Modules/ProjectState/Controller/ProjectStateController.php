<?php
namespace App\Modules\ProjectState\Controller;
use App\Http\Requests\ProjectStateRequest;
use App\Http\Resources\ProjectStateResource;
use App\Modules\ProjectState\Service\ProjectStateService;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;
use RuntimeException;

class ProjectStateController extends Controller
{
    private ProjectStateService $projectStateService;

    public function __construct(ProjectStateService $projectStateService)
    {
        $this->projectStateService = $projectStateService;
    }

    // Metodos a futuro
    public function index()
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

    public function show($id)
    {
        try {
            $state = $this->projectStateService->getProjectStateById($id);
            return ApiResponse::success(
                'Estado encontrado',
                200,
                new ProjectStateResource($state),
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Estado del proyecto');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
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
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
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
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

}