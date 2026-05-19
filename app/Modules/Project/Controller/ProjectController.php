<?php

namespace App\Modules\Project\Controller;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectDashboardRowResource;
use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\SimpleProjectResource;
use App\Http\Responses\ApiResponse;
use App\Modules\Project\Service\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="Project",
 *     type="object",
 *     title="Project",
 *     description="Proyectos con información completa de planificación, presupuesto, progreso y relaciones",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del proyecto"),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Proyecto de Educación Rural",
 *         description="Nombre del proyecto (único, máximo 255 caracteres)"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         example="Mejora de la calidad educativa en comunidades rurales de Potosí",
 *         description="Descripción detallada del proyecto"
 *     ),
 *     @OA\Property(
 *         property="project_url",
 *         type="string",
 *         example="https://example.com/proyecto-educacion-rural",
 *         description="URL del proyecto (opcional)"
 *     ),
 *     @OA\Property(
 *         property="start_date",
 *         type="string",
 *         format="date",
 *         example="2025-01-15",
 *         description="Fecha de inicio del proyecto"
 *     ),
 *     @OA\Property(
 *         property="end_date",
 *         type="string",
 *         format="date",
 *         example="2027-12-31",
 *         description="Fecha de finalización del proyecto"
 *     ),
 *     @OA\Property(
 *         property="progress",
 *         type="number",
 *         format="float",
 *         example=45.5,
 *         description="Porcentaje de progreso (0-100)"
 *     ),
 *     @OA\Property(
 *         property="comments",
 *         type="string",
 *         example="Se requiere revisión del presupuesto en Q2",
 *         description="Comentarios adicionales"
 *     ),
 *     @OA\Property(
 *         property="project_budget",
 *         type="number",
 *         format="float",
 *         example=500000.00,
 *         description="Presupuesto total del proyecto"
 *     ),
 *     @OA\Property(
 *         property="contact_id",
 *         type="integer",
 *         example=2,
 *         description="ID del contacto responsable (FK a contact)"
 *     ),
 *     @OA\Property(
 *         property="beneficiary_id",
 *         type="integer",
 *         example=3,
 *         description="ID del beneficiario (FK a beneficiary)"
 *     ),
 *     @OA\Property(
 *         property="project_state_id",
 *         type="integer",
 *         example=1,
 *         description="ID del estado del proyecto (FK a project_state)"
 *     ),
 *     @OA\Property(
 *         property="donors",
 *         type="array",
 *         description="Lista de donantes asociados (cargado opcionalmente)",
 *         @OA\Items(ref="#/components/schemas/Donor")
 *     )
 * )
 */
class ProjectController extends Controller
{
    private ProjectService $projectService;
    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * @OA\Get(
     *     path="/projects",
     *     tags={"Projects"},
     *     summary="Listar proyectos",
     *     description="Obtiene todos los proyectos con sus donantes asociados cargados",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de proyectos obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de proyectos obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="projects",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Project")
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
            $projects = $this->projectService->getAllProjects();

            $projects->load("donors");

            return ApiResponse::success(
                'Project list successfully obtained',
                200,
                [
                    'projects' => ProjectResource::collection($projects),
                    'total' => count($projects)
                ]
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/projects/{id}",
     *     tags={"Projects"},
     *     summary="Obtener proyecto por ID",
     *     description="Obtiene la información completa de un proyecto específico",
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
     *         description="Proyecto encontrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Proyecto encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Project")
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
    public function show($id)
    {
        try {
            $project = $this->projectService->findProjectById($id);

            return ApiResponse::success(
                'Project Found',
                200,
                new ProjectResource($project)
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::notFound('Project');
        } catch (\Exception $e) {
            return ApiResponse::error($e, 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/projects",
     *     tags={"Projects"},
     *     summary="Crear proyecto",
     *     description="Crea un nuevo proyecto con toda su información de planificación, presupuesto y relaciones. El nombre debe ser único.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "description", "start_date", "end_date", "progress", "comments", "project_budget", "contact_id", "beneficiary_id", "project_state_id"},
     *                 @OA\Property(property="name", type="string", example="Proyecto de Salud Comunitaria", description="Nombre del proyecto (requerido, único, máximo 255 caracteres)"),
     *                 @OA\Property(property="description", type="string", example="Fortalecimiento de servicios de salud en comunidades rurales", description="Descripción detallada (requerido)"),
     *                 @OA\Property(property="project_url", type="string", example="https://example.com/salud-comunitaria", description="URL del proyecto (opcional)"),
     *                 @OA\Property(property="start_date", type="string", format="date", example="2025-02-01", description="Fecha de inicio (requerido, formato: YYYY-MM-DD)"),
     *                 @OA\Property(property="end_date", type="string", format="date", example="2028-01-31", description="Fecha de finalización (requerido, formato: YYYY-MM-DD, debe ser posterior a start_date)"),
     *                 @OA\Property(property="progress", type="number", format="float", example=0, description="Porcentaje de progreso (requerido, 0-100)"),
     *                 @OA\Property(property="comments", type="string", example="Proyecto en fase de planificación", description="Comentarios (requerido)"),
     *                 @OA\Property(property="project_budget", type="number", format="float", example=750000.00, description="Presupuesto total (requerido, mayor a 0)"),
     *                 @OA\Property(property="contact_id", type="integer", example=3, description="ID del contacto responsable (requerido, debe existir en contact)"),
     *                 @OA\Property(property="beneficiary_id", type="integer", example=2, description="ID del beneficiario (requerido, debe existir en beneficiary)"),
     *                 @OA\Property(property="project_state_id", type="integer", example=1, description="ID del estado (requerido, debe existir en project_state)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Proyecto creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Proyecto creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Project")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="name", type="array", @OA\Items(type="string", example="El nombre del proyecto es obligatorio")),
     *                 @OA\Property(property="start_date", type="array", @OA\Items(type="string", example="La fecha de inicio es obligatoria")),
     *                 @OA\Property(property="end_date", type="array", @OA\Items(type="string", example="La fecha de fin debe ser posterior a la fecha de inicio")),
     *                 @OA\Property(property="project_budget", type="array", @OA\Items(type="string", example="El presupuesto debe ser mayor a 0"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function store(ProjectRequest $request)
    {
        try {
            $validated = $request->validated();

            $userPermissions = auth('api')->user()->getAllPermissions()->pluck('name')->toArray();
            $isAdmin = in_array('*:*', $userPermissions);
            $hasWeightPerm = $isAdmin || in_array('projects:weight', $userPermissions);
            $weight = $hasWeightPerm ? (float) $validated['weight'] : 0.0;

            $project = $this->projectService->createProject(
                $validated['program_id'],
                $validated['name'],
                $validated['description'],
                $validated['project_url'] ?? null,
                $validated['start_date'],
                $validated['end_date'],
                $validated['progress'],
                $validated['comments'] ?? '',
                $validated['budget'],
                $weight,
                $validated['indicators'],
                $validated['donors'],
                $validated['agencies'],
                $validated['contact'],
                $validated['beneficiary'],
                $validated['project_state']
            );

            return ApiResponse::created(
                'Project created successfully',
                new ProjectResource($project)
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            dd($e);
            return ApiResponse::error("Internal server error", 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/projects/{id}",
     *     tags={"Projects"},
     *     summary="Actualizar proyecto",
     *     description="Actualiza la información completa de un proyecto existente. El nombre debe ser único.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del proyecto a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "description", "start_date", "end_date", "progress", "comments", "project_budget", "contact_id", "beneficiary_id", "project_state_id"},
     *                 @OA\Property(property="name", type="string", example="Proyecto de Salud Comunitaria - Fase 2", description="Nuevo nombre del proyecto (requerido, único)"),
     *                 @OA\Property(property="description", type="string", example="Fortalecimiento y expansión de servicios de salud", description="Nueva descripción (requerido)"),
     *                 @OA\Property(property="project_url", type="string", example="https://example.com/salud-fase2", description="Nueva URL (opcional)"),
     *                 @OA\Property(property="start_date", type="string", format="date", example="2025-02-01", description="Nueva fecha de inicio (requerido)"),
     *                 @OA\Property(property="end_date", type="string", format="date", example="2028-06-30", description="Nueva fecha de finalización (requerido)"),
     *                 @OA\Property(property="progress", type="number", format="float", example=65.5, description="Nuevo porcentaje de progreso (requerido, 0-100)"),
     *                 @OA\Property(property="comments", type="string", example="Proyecto en ejecución, buen avance", description="Nuevos comentarios (requerido)"),
     *                 @OA\Property(property="project_budget", type="number", format="float", example=850000.00, description="Nuevo presupuesto (requerido)"),
     *                 @OA\Property(property="contact_id", type="integer", example=4, description="Nuevo ID de contacto (requerido)"),
     *                 @OA\Property(property="beneficiary_id", type="integer", example=2, description="Nuevo ID de beneficiario (requerido)"),
     *                 @OA\Property(property="project_state_id", type="integer", example=2, description="Nuevo ID de estado (requerido)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proyecto actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Proyecto actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Project")
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
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="name", type="array", @OA\Items(type="string", example="Este proyecto ya existe en el sistema"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function update(ProjectRequest $request, int $id)
    {
        try {
            $validated = $request->validated();

            $userPermissions = auth('api')->user()->getAllPermissions()->pluck('name')->toArray();
            $isAdmin = in_array('*:*', $userPermissions);
            $hasWeightPerm = $isAdmin || in_array('projects:weight', $userPermissions);

            $isWeightOnlyUpdate = $request->has('weight') && count($request->all()) === 1;

            if ($hasWeightPerm && !$isAdmin && $isWeightOnlyUpdate) {
                $project = $this->projectService->updateProjectWeight($id, (float) ($validated['weight'] ?? 0));
                return ApiResponse::success('Project weight updated successfully', 200, new ProjectResource($project));
            }

            $weight = $hasWeightPerm
                ? (float) $validated['weight']
                : (float) $this->projectService->findProjectById($id)->weight;

            $project = $this->projectService->updateProject(
                $id,
                $validated['program_id'],
                $validated['name'],
                $validated['description'],
                $validated['project_url'] ?? null,
                $validated['start_date'],
                $validated['end_date'],
                $validated['progress'],
                $validated['comments'] ?? '',
                $validated['budget'],
                $weight,
                $validated['indicators'],
                $validated['donors'],
                $validated['agencies'],
                $validated['contact'],
                $validated['beneficiary'],
                $validated['project_state']
            );

            return ApiResponse::success(
                'Project uploaded successfully',
                200,
                new ProjectResource($project)
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error($e, 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/projects/search",
     *     tags={"Projects"},
     *     summary="Buscar proyecto por nombre",
     *     description="Busca un proyecto específico por su nombre (búsqueda exacta, case-insensitive)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Nombre del proyecto a buscar",
     *         @OA\Schema(type="string", example="Proyecto de Educación Rural")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proyecto encontrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Proyecto encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Project")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Proyecto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Project no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Parámetro name inválido",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo name es obligatorio")
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
    public function search(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|min:1',
            ]);

            $project = $this->projectService->getProjectByName($request->input('name'));

            return ApiResponse::success(
                'Project found',
                200,
                new ProjectResource($project)
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::notFound('Project');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function getProjectsByProgramId(Request $request, int $programId)
    {
        try {
            $search = $request->get("search");
            $perPage = (int) $request->get("per_page", 10);

            $projects = $this->projectService->findProjectByProgramIdPaginated($programId, $search, $perPage);

            return ApiResponse::success(
                'Projects paginated of selected program list successfully uploaded',
                200,
                [
                    'projects' => SimpleProjectResource::collection($projects),
                    'total' => $projects->total(),
                    'per_page' => $projects->perPage(),
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function getDashboardProjects(Request $request)
    {
        try {
            $search = $request->get('search');
            $perPage = (int) $request->get('per_page', 10);
            $countryId = (int) $request->get('country_id', 0);

            $projects = $this->projectService->getDashboardProjectsPaginated($search, $perPage, $countryId);

            return ApiResponse::success(
                'Project dashboard list successfully obtained',
                200,
                [
                    'projects' => ProjectDashboardRowResource::collection($projects),
                    'total' => $projects->total(),
                    'per_page' => $projects->perPage(),
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function updateDashboardProgress(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'progress' => 'required|numeric|min:0|max:100',
            ]);

            $project = $this->projectService->updateProjectProgress($id, (float) $validated['progress']);

            return ApiResponse::success(
                'Project progress updated successfully',
                200,
                [
                    'id' => (int) $project->id,
                    'progress' => (float) $project->progress,
                ]
            );
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function updateDashboardWeight(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'weight' => 'required|numeric|min:0|max:1',
            ]);

            $project = $this->projectService->updateProjectWeight($id, (float) $validated['weight']);

            return ApiResponse::success(
                'Project weight updated successfully',
                200,
                [
                    'id' => (int) $project->id,
                    'weight' => (float) $project->weight,
                ]
            );
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->projectService->deleteProject($id);

            return ApiResponse::success(
                'Project deleted successfully',
                200
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function getProgramKpas(Request $request, int $programId)
    {
        try {
            $search = $request->get('search');
            $perPage = (int) $request->get('per_page', 10);

            /** @var \Illuminate\Pagination\LengthAwarePaginator $countryKpas */
            $countryKpas = $this->projectService->getProgramKpasForCurrentUser($programId, $search, $perPage);

            $kpaNumberMap = $this->projectService->getProgramKpaNumberMap($programId);

            $kpas = $countryKpas->getCollection()->map(function ($countryKpa) use ($kpaNumberMap) {
                $kpaId = $countryKpa->kpa?->id;
                $payload = [
                    'id' => $kpaId,
                    'name' => $countryKpa->kpa?->name,
                    'strategic_outputs_count' => (int) ($countryKpa->strategic_outputs_count ?? 0),
                ];

                if ($kpaId && array_key_exists($kpaId, $kpaNumberMap)) {
                    $payload['numbering'] = (string) $kpaNumberMap[$kpaId];
                }

                return $payload;
            })->values();

            return ApiResponse::success(
                'Program KPAs paginated list successfully uploaded',
                200,
                [
                    'kpas' => $kpas,
                    'total' => $countryKpas->total(),
                    'per_page' => $countryKpas->perPage(),
                    'current_page' => $countryKpas->currentPage(),
                    'last_page' => $countryKpas->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function getProgramStrategicOutputsByKpa(Request $request, int $programId, int $kpaId)
    {
        try {
            $search = $request->get('search');
            $perPage = (int) $request->get('per_page', 10);

            $strategicOutputs = $this->projectService->getProgramStrategicOutputsForCurrentUser($programId, $kpaId, $search, $perPage);

            $kpaNumberMap = $this->projectService->getProgramKpaNumberMap($programId);
            $strategicOutputNumberMap = $this->projectService->getProgramStrategicOutputNumberMap($programId, $kpaId);

            $kpaNumber = array_key_exists($kpaId, $kpaNumberMap) ? (string) $kpaNumberMap[$kpaId] : '';

            $strategicOutputs->getCollection()->transform(function ($strategicOutput) use ($kpaNumber, $strategicOutputNumberMap) {
                if ($kpaNumber === '' || !array_key_exists($strategicOutput->id, $strategicOutputNumberMap)) {
                    return $strategicOutput;
                }

                $strategicOutput->numbering = $kpaNumber . '.' . $strategicOutputNumberMap[$strategicOutput->id];

                return $strategicOutput;
            });

            return ApiResponse::success(
                'Program strategic outputs paginated list successfully uploaded',
                200,
                [
                    'strategic_outputs' => \App\Http\Resources\StrategicOutputResource::collection($strategicOutputs),
                    'total' => $strategicOutputs->total(),
                    'per_page' => $strategicOutputs->perPage(),
                    'current_page' => $strategicOutputs->currentPage(),
                    'last_page' => $strategicOutputs->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function getProgramMeasuresByStrategicOutput(Request $request, int $programId, int $strategicOutputId)
    {
        try {
            $search = $request->get('search');
            $perPage = (int) $request->get('per_page', 10);

            $measures = $this->projectService->getProgramMeasuresForCurrentUser($programId, $strategicOutputId, $search, $perPage);

            $context = $this->projectService->getProgramStrategicOutputContext($programId, $strategicOutputId);
            $measureNumberMap = $this->projectService->getProgramMeasureNumberMap($strategicOutputId);

            $kpaNumber = $context['kpa_number'] ?? '';
            $strategicOutputNumber = $context['strategic_output_number'] ?? '';

            $measures->getCollection()->transform(function ($measure) use ($kpaNumber, $strategicOutputNumber, $measureNumberMap) {
                if ($kpaNumber === '' || $strategicOutputNumber === '' || !array_key_exists($measure->id, $measureNumberMap)) {
                    return $measure;
                }

                $measure->numbering = $kpaNumber . '.' . $strategicOutputNumber . '.' . $measureNumberMap[$measure->id];

                return $measure;
            });

            return ApiResponse::success(
                'Program measures paginated list successfully uploaded',
                200,
                [
                    'measures' => \App\Http\Resources\MeasureResource::collection($measures),
                    'total' => $measures->total(),
                    'per_page' => $measures->perPage(),
                    'current_page' => $measures->currentPage(),
                    'last_page' => $measures->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function getProgramIndicatorsByMeasure(Request $request, int $programId, int $measureId)
    {
        try {
            $search = $request->get('search');
            $perPage = (int) $request->get('per_page', 10);
            $exclude = (array) $request->input('exclude', []);

            $indicators = $this->projectService->getProgramIndicatorsForCurrentUser($programId, $measureId, $search, $perPage, $exclude);

            return ApiResponse::success(
                'Program indicators paginated list successfully uploaded',
                200,
                [
                    'indicators' => \App\Http\Resources\IndicatorResource::collection($indicators),
                    'total' => $indicators->total(),
                    'per_page' => $indicators->perPage(),
                    'current_page' => $indicators->currentPage(),
                    'last_page' => $indicators->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
