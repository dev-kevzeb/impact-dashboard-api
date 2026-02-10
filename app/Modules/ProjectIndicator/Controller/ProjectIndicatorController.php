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

/**
 * @OA\Schema(
 *     schema="ProjectIndicator",
 *     type="object",
 *     title="ProjectIndicator",
 *     description="Relación muchos-a-muchos entre proyectos e indicadores de desempeño",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único de la relación"),
 *     @OA\Property(property="project_id", type="integer", example=1, description="ID del proyecto (FK a project)"),
 *     @OA\Property(property="indicator_id", type="integer", example=3, description="ID del indicador (FK a indicator)"),
 *     @OA\Property(
 *         property="project",
 *         type="object",
 *         description="Información del proyecto (cargado opcionalmente)",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Proyecto de Educación Rural")
 *     ),
 *     @OA\Property(
 *         property="indicator",
 *         type="object",
 *         description="Información del indicador (cargado opcionalmente)",
 *         @OA\Property(property="id", type="integer", example=3),
 *         @OA\Property(property="name", type="string", example="Tasa de alfabetización")
 *     )
 * )
 */
class ProjectIndicatorController extends Controller
{
    private ProjectIndicatorService $projectIndicatorService;

    public function __construct(ProjectIndicatorService $projectIndicatorService)
    {
        $this->projectIndicatorService = $projectIndicatorService;
    }

    /**
     * @OA\Get(
     *     path="/project_indicators",
     *     tags={"Project-Indicators"},
     *     summary="Listar relaciones Proyecto-Indicador",
     *     description="Obtiene todas las relaciones entre proyectos e indicadores con sus detalles cargados",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de proyecto-indicador obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="projectIndicators",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/ProjectIndicator")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=20)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
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
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/project_indicators/projects/indicator/{id}",
     *     tags={"Project-Indicators"},
     *     summary="Obtener proyectos por ID de indicador",
     *     description="Obtiene todos los proyectos asociados a un indicador específico por su ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del indicador",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de proyectos obtenida",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de proyectos para el indicatdor con id 1 recueprada exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="projects",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Project")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Indicador no encontrado o sin proyectos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se encontraron proyectos para este indicador")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function showProjectsByIndicatorId(int $id)
    {
        try {
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

    /**
     * @OA\Get(
     *     path="/project_indicators/indicators/project/{id}",
     *     tags={"Project-Indicators"},
     *     summary="Obtener indicadores por ID de proyecto",
     *     description="Obtiene todos los indicadores asociados a un proyecto específico por su ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del proyecto",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de indicadores obtenida",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de indicadores para el proyecto con id 1. recuperada exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="indicators",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Indicator")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=8)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Proyecto no encontrado o sin indicadores",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se encontraron indicadores para este proyecto")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function showIndicatorsByProjectId(int $id)
    {
        try {
            $indicators = $this->projectIndicatorService->findIndicatorsByProjectId($id);

            return ApiResponse::success(
                "Lista de indicadores para el proyecto con id {$id}. recuperada exitosamente",
                200,
                [
                    'indicators' => IndicatorResource::collection($indicators),
                    'total' => count($indicators),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor.", 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/project_indicators/indicators/project-name/{name}",
     *     tags={"Project-Indicators"},
     *     summary="Obtener indicadores por nombre de proyecto",
     *     description="Obtiene todos los indicadores asociados a un proyecto específico por su nombre",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         required=true,
     *         description="Nombre del proyecto",
     *         @OA\Schema(type="string", example="Proyecto de Educación Rural")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de indicadores obtenida",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de indicadores asociadas al proyecto 'Proyecto de Educación Rural' recuperada exitosamente."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="indicators",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Indicator")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=6)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Proyecto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Proyecto no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function showIndicatorsByPRojectName(string $name)
    {
        try {
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

    /**
     * @OA\Post(
     *     path="/project_indicators",
     *     tags={"Project-Indicators"},
     *     summary="Crear relación Proyecto-Indicador",
     *     description="Asocia un indicador de desempeño a un proyecto específico",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"project_id", "indicator_id"},
     *                 @OA\Property(property="project_id", type="integer", example=1, description="ID del proyecto (requerido, debe existir en project)"),
     *                 @OA\Property(property="indicator_id", type="integer", example=3, description="ID del indicador (requerido, debe existir en indicator)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Relación creada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Relación creada correctamente entre el proyecto 1 y el indicador 3."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="projectIndicator", ref="#/components/schemas/ProjectIndicator")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación o FKs inválidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Los campos project_id e indicator_id son obligatorios.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function createProjectIndicator(Request $request)
    {
        try {
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
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor. {$e}", 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/project_indicators",
     *     tags={"Project-Indicators"},
     *     summary="Eliminar relación Proyecto-Indicador",
     *     description="Desasocia un indicador de un proyecto eliminando el registro de la relación",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"project_indicator_id"},
     *                 @OA\Property(property="project_indicator_id", type="integer", example=1, description="ID de la relación project_indicator a eliminar (requerido)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="La relación project-indicator con id 1 fue eliminada correctamente.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relación no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Relación no encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function deleteProjectIndicator(Request $request)
    {
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
