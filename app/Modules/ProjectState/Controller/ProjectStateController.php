<?php

namespace App\Modules\ProjectState\Controller;

use App\Http\Requests\ProjectStateRequest;
use App\Http\Resources\ProjectStateResource;
use App\Modules\ProjectState\Service\ProjectStateService;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="ProjectState",
 *     type="object",
 *     title="ProjectState",
 *     description="Estados del ciclo de vida de un proyecto",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del estado"),
 *     @OA\Property(
 *         property="state",
 *         type="string",
 *         example="En Ejecución",
 *         description="Nombre del estado del proyecto (único). Ejemplos: 'Planificación', 'En Ejecución', 'Finalizado', 'Suspendido'"
 *     )
 * )
 */
class ProjectStateController extends Controller
{
    private ProjectStateService $projectStateService;

    public function __construct(ProjectStateService $projectStateService)
    {
        $this->projectStateService = $projectStateService;
    }

    /**
     * @OA\Get(
     *     path="/project_states",
     *     tags={"Project States"},
     *     summary="Listar estados de proyectos",
     *     description="Obtiene todos los estados del ciclo de vida de proyectos (Planificación, En Ejecución, Finalizado, etc.)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de estados obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de estados obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ProjectState")
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
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->get("search");
            $perPage = (int) $request->get("per_page", 10);

            $states = $this->projectStateService->getProjectStatesPaginated($search, $perPage);

            return ApiResponse::success(
                'Project statuses list successfully obtained',
                200,
                [
                    'project_states' => ProjectStateResource::collection($states),
                    'total' => $states->count(),
                    'per_page' => $states->perPage(),
                    'current_page' => $states->currentPage(),
                    'last_page' => $states->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/project_states/{id}",
     *     tags={"Project States"},
     *     summary="Obtener estado de proyecto por ID",
     *     description="Obtiene la información de un estado específico del proyecto",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del estado del proyecto",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estado encontrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estado del proyecto encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProjectState")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estado no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Estado del proyecto no encontrado")
     *         )
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $state = $this->projectStateService->getProjectStateById($id);
            return ApiResponse::success(
                'Project status found',
                200,
                new ProjectStateResource($state),
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Project Status');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/project_states",
     *     tags={"Project States"},
     *     summary="Crear estado de proyecto",
     *     description="Crea un nuevo estado para el ciclo de vida del proyecto. El nombre debe ser único.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"state"},
     *                 @OA\Property(
     *                     property="state",
     *                     type="string",
     *                     example="En Revisión",
     *                     description="Nombre del estado (requerido, único, máximo 255 caracteres)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Estado creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estado del proyecto creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProjectState")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de negocio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="El nombre del estado no debe ir vacío")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación técnica",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="state",
     *                     type="array",
     *                     @OA\Items(type="string", example="El nombre del estado es obligatorio")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function store(ProjectStateRequest $request)
    {
        try {
            $validated = $request->validated();
            $state = $this->projectStateService->createProjectState($validated['state']);
            return ApiResponse::success(
                'Project status created successfully',
                201,
                new ProjectStateResource($state)
            );
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['state' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/project_states/{id}",
     *     tags={"Project States"},
     *     summary="Actualizar estado de proyecto",
     *     description="Actualiza el nombre de un estado existente. El nuevo nombre debe ser único.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del estado a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"state"},
     *                 @OA\Property(
     *                     property="state",
     *                     type="string",
     *                     example="Pausado",
     *                     description="Nuevo nombre del estado (requerido, único, máximo 255 caracteres)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estado actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estado del proyecto actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProjectState")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de negocio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="El nombre del estado debe tener al menos 2 caracteres")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estado no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Estado del proyecto no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación técnica",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="state",
     *                     type="array",
     *                     @OA\Items(type="string", example="Este estado ya existe en el sistema")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function update(ProjectStateRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $state = $this->projectStateService->updateProjectState($id, $validated['state']);
            return ApiResponse::success(
                'Project status updated successfully',
                200,
                new ProjectStateResource($state)
            );
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['state' => [$e->getMessage()]]);
            }
            if (str_contains($e->getMessage(), 'no encontrado')) {
                return ApiResponse::notFound('Project Status');
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->projectStateService->deleteProjectState($id);

            return ApiResponse::success(
                'Project status deleted successfully',
                200
            );
        } catch (RuntimeException $e) {
            if (str_contains(strtolower($e->getMessage()), 'not found') || str_contains(strtolower($e->getMessage()), 'does not exist')) {
                return ApiResponse::notFound('Project Status');
            }

            if (str_contains(strtolower($e->getMessage()), 'cannot be deleted')) {
                return ApiResponse::error($e->getMessage(), 409);
            }

            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/project_states/search",
     *     tags={"Project States"},
     *     summary="Buscar estado de proyecto por nombre",
     *     description="Busca un estado específico por su nombre (búsqueda exacta, case-insensitive)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Nombre del estado a buscar",
     *         @OA\Schema(type="string", example="En Ejecución")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estado encontrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estado del proyecto encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProjectState")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Parámetro name no proporcionado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="El parámetro name es requerido")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estado no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Estado del proyecto no encontrado")
     *         )
     *     )
     * )
     */
    public function search(): JsonResponse
    {
        try {
            $name = request()->query('name');

            if (!$name) {
                return ApiResponse::error('El parámetro name es requerido', 400);
            }

            $state = $this->projectStateService->findProjectStateByName($name);

            return ApiResponse::success(
                'Project status found',
                200,
                new ProjectStateResource($state)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Project Status');
        }
    }
}
