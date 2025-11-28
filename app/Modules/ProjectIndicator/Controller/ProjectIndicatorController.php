<?php

namespace App\Modules\ProjectIndicator\Controller;

use App\Http\Controllers\Controller;
use App\Http\Resources\IndicatorResource;
use App\Http\Resources\ProjectIndicatorResource;
use App\Http\Resources\ProjectResource;
use App\Http\Responses\ApiResponse;
use App\Modules\ProjectIndicator\Service\ProjectIndicatorService;
use Illuminate\Http\Request;
use RuntimeException;

class ProjectIndicatorController extends Controller
{
    private ProjectIndicatorService $projectIndicatorService;

    public function __construct(ProjectIndicatorService $projectIndicatorService)
    {
        $this->projectIndicatorService = $projectIndicatorService;
    }

    public function index()
    {
       try{
            $projectIndicators = $this->projectIndicatorService->getAllProjectIndicators();
            $projectIndicators->load(["project", "indicator"]);

            return ApiResponse::success(
                'Lista de proyecto-indicador obtenida exitosamente',
                200,
                [
                    'projectIndicators' => ProjectIndicatorResource::collection($projectIndicators),
                    'total' => count($projectIndicators),
                ]
            );
       }catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function showProjectsByIndicatorId(int $id)
    {
        try{
            $projects = $this->projectIndicatorService->findProjectsByIndicatorId($id); 

            return ApiResponse::success(
                "Lista de proyectos para el indicatdor con id {$id} recueprada exitosamente",
                200,
                [
                    'projects' => ProjectResource::collection($projects),
                    'total' => count($projects),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor.", 500);
        }
    }

    public function showIndicatorsByProjectId(int $id)
    {
        try{
            $indicators = $this->projectIndicatorService->findIndicatorsByProjectId($id);

            return ApiResponse::success(
                "Lista de indicadores para el proyecto con id {$id}. recuperada exitosamente",
                200,
                [
                    'indicators'=> IndicatorResource::collection($indicators),
                    'total' => count($indicators),
                ]
             );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor.", 500);
        }
    }

    public function showIndicatorsByPRojectName(string $name)
    {
        try{
            $indicators = $this->projectIndicatorService->findIndicatorsByProjectName($name);
            return ApiResponse::success(
                    "Lista de indicadores asociadas al proyecto '{$name}' recuperada exitosamente.",
                    200,
                    [
                        'indicators' => IndicatorResource::collection($indicators),
                        'total' => count($indicators)
                    ]
                );  
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor.", 500);
        }
    }

    public function createProjectIndicator(Request $request)
    {
        try{
            $projectId = $request->input('project_id');
            $indicatorId = $request->input('indicator_id');

            if (!$projectId || !$indicatorId) throw new RuntimeException("Los campos project_id e indicator_id son obligatorios.");

            $projectIndicator = $this->projectIndicatorService->createProjectIndicator($projectId, $indicatorId);

            $projectIndicator->load(['project', 'indicator']);

            return ApiResponse::success(
                "Relación creada correctamente entre el proyecto {$projectId} y el indicador {$indicatorId}.",
                201,
                [
                    'projectIndicator' => new ProjectIndicatorResource($projectIndicator)
                ]
            );
        }  catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor. {$e}", 500);
        }
    }

    public function deleteProjectIndicator(Request $request){
        try {
            $projectIndicatorId = $request->input('project_indicator_id');
            if (!$projectIndicatorId) throw new RuntimeException("El campo project_indicator_id es obligatorio.");
            $this->projectIndicatorService->deleteProjectIndicator($projectIndicatorId);

            return ApiResponse::success(
                "La relación project-indicator con id {$projectIndicatorId} fue eliminada correctamente.",
                200
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor.", 500);
        }
    }
}