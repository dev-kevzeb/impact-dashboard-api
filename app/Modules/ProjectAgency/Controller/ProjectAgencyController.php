<?php

namespace App\Modules\ProjectAgency\Controller;

use App\Http\Resources\AgencyResource;
use App\Http\Resources\ProjectAgencyResource;
use App\Http\Resources\ProjectResource;
use App\Modules\ProjectAgency\Service\ProjectAgencyService;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="ProjectAgency",
 *     type="object",
 *     title="ProjectAgency",
 *     description="Relación muchos-a-muchos entre proyectos y agencias ejecutoras",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único de la relación"),
 *     @OA\Property(property="project_id", type="integer", example=1, description="ID del proyecto (FK a project)"),
 *     @OA\Property(property="agency_id", type="integer", example=2, description="ID de la agencia (FK a agency)"),
 *     @OA\Property(
 *         property="project",
 *         type="object",
 *         description="Información del proyecto (cargado opcionalmente)",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Proyecto Educación Rural")
 *     ),
 *     @OA\Property(
 *         property="agency",
 *         type="object",
 *         description="Información de la agencia (cargado opcionalmente)",
 *         @OA\Property(property="id", type="integer", example=2),
 *         @OA\Property(property="name", type="string", example="UNICEF Bolivia")
 *     )
 * )
 */
class ProjectAgencyController extends Controller
{
    private ProjectAgencyService $projectAgencyService;

    public function __construct(ProjectAgencyService $projectAgencyService)
    {
        $this->projectAgencyService = $projectAgencyService;
    }

    /**
     * @OA\Get(
     *     path="/project_agencies",
     *     tags={"Project-Agencies"},
     *     summary="Listar relaciones Proyecto-Agencia",
     *     description="Obtiene todas las relaciones entre proyectos y agencias ejecutoras con sus detalles cargados",
     *     @OA\Response(
     *         response=200,
     *         description="Lista obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de relaciones proyecto-agencia obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="projectAgencies",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/ProjectAgency")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=15)
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
            $projectAgencies = $this->projectAgencyService->getAllProjectAgencies();

            $projectAgencies->load(["project", "agency"]);

            return ApiResponse::success(
                'Lista de relaciones proyecto-agencia obtenida exitosamente',
                200,
                [
                    'projectAgencies' => ProjectAgencyResource::collection($projectAgencies),
                    'total' => count($projectAgencies)
                ]
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/project_agencies/projects-by-agency/{id}",
     *     tags={"Project-Agencies"},
     *     summary="Obtener proyectos por ID de agencia",
     *     description="Obtiene todos los proyectos asociados a una agencia específica por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la agencia",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de proyectos obtenida",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de proyectos para la agencia con id 1."),
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
     *         description="Agencia no encontrada o sin proyectos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se encontraron proyectos para esta agencia")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function showProjectsByAgencyId(int $id)
    {
        try {
            $projects = $this->projectAgencyService->findProjectsByAgencyId($id);

            return ApiResponse::success(
                "Lista de proyectos para la agencia con id {$id}.",
                200,
                [
                    'projects' => ProjectResource::collection($projects),
                    'total' => count($projects)
                ]
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor.", 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/project_agencies/projects-by-agency-name/{name}",
     *     tags={"Project-Agencies"},
     *     summary="Obtener proyectos por nombre de agencia",
     *     description="Obtiene todos los proyectos asociados a una agencia específica por su nombre",
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         required=true,
     *         description="Nombre de la agencia",
     *         @OA\Schema(type="string", example="UNICEF Bolivia")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de proyectos obtenida",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de proyectos asociados a la agencia 'UNICEF Bolivia'."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="projects",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Project")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=3)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Agencia no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Agencia no encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function showProjectsByAgencyName(string $name)
    {
        try {
            $projects = $this->projectAgencyService->findProjectsByAgencyName($name);

            return ApiResponse::success(
                "Lista de proyectos asociados a la agencia '{$name}'.",
                200,
                [
                    'projects' => ProjectResource::collection($projects),
                    'total' => count($projects)
                ]
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor.", 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/project_agencies/agencies-by-project/{id}",
     *     tags={"Project-Agencies"},
     *     summary="Obtener agencias por ID de proyecto",
     *     description="Obtiene todas las agencias asociadas a un proyecto específico por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del proyecto",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de agencias obtenida",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de agencias para el proyecto con id 1."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="agencies",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Agency")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=4)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Proyecto no encontrado o sin agencias",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se encontraron agencias para este proyecto")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function showAgenciesByProjectId(int $id)
    {
        try {
            $agencies = $this->projectAgencyService->findAgenciesByProjectId($id);

            return ApiResponse::success(
                "Lista de agencias para el proyecto con id {$id}.",
                200,
                [
                    'agencies' => AgencyResource::collection($agencies),
                    'total' => count($agencies)
                ]
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor.", 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/project_agencies/agencies-by-project-name/{name}",
     *     tags={"Project-Agencies"},
     *     summary="Obtener agencias por nombre de proyecto",
     *     description="Obtiene todas las agencias asociadas a un proyecto específico por su nombre",
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         required=true,
     *         description="Nombre del proyecto",
     *         @OA\Schema(type="string", example="Proyecto Educación Rural")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de agencias obtenida",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de agencias asociadas al proyecto 'Proyecto Educación Rural'."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="agencies",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Agency")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=2)
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
    public function showAgenciesByProjectName(string $name)
    {
        try {
            $agencies = $this->projectAgencyService->findAgenciesByProjectName($name);

            return ApiResponse::success(
                "Lista de agencias asociadas al proyecto '{$name}'.",
                200,
                [
                    'agencies' => AgencyResource::collection($agencies),
                    'total' => count($agencies)
                ]
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor.", 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/project_agencies",
     *     tags={"Project-Agencies"},
     *     summary="Crear relación Proyecto-Agencia",
     *     description="Asocia una agencia ejecutora a un proyecto específico",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"project_id", "agency_id"},
     *                 @OA\Property(property="project_id", type="integer", example=1, description="ID del proyecto (requerido, debe existir en project)"),
     *                 @OA\Property(property="agency_id", type="integer", example=2, description="ID de la agencia (requerido, debe existir en agency)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Relación creada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Relación creada correctamente entre el proyecto 1 y la agencia 2."),
     *             @OA\Property(property="data", ref="#/components/schemas/ProjectAgency")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación o FKs inválidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Los campos project_id y agency_id son obligatorios.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function createProjectAgency(Request $request)
    {
        try {
            $projectId = $request->input('project_id');
            $agencyId = $request->input('agency_id');
            if (!$projectId || !$agencyId) throw new \RuntimeException("Los campos project_id y agency_id son obligatorios.");

            $projectAgency = $this->projectAgencyService->createProjectAgency($projectId, $agencyId);
            $projectAgency->load(['project', 'agency']);

            return ApiResponse::success(
                "Relación creada correctamente entre el proyecto {$projectId} y la agencia {$agencyId}.",
                201,
                new ProjectAgencyResource($projectAgency)
            );

        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor.", 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/project_agencies",
     *     tags={"Project-Agencies"},
     *     summary="Eliminar relación Proyecto-Agencia",
     *     description="Desasocia una agencia de un proyecto eliminando el registro de la relación",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"project_agency_id"},
     *                 @OA\Property(property="project_agency_id", type="integer", example=1, description="ID de la relación project_agency a eliminar (requerido)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="La relación project-agency con id 1 fue eliminada correctamente.")
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
    public function deleteProjectAgency(Request $request)
    {
        try {
            $projectAgencyId = $request->input('project_agency_id');
            if (!$projectAgencyId) throw new \RuntimeException("El campo project_agency_id es obligatorio.");
            $this->projectAgencyService->deleteProjectAgency($projectAgencyId);

            return ApiResponse::success(
                "La relación project-agency con id {$projectAgencyId} fue eliminada correctamente.",
                200
            );

        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor.", 500);
        }
    }
}