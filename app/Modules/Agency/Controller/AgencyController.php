<?php

namespace App\Modules\Agency\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgencyRequest;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\AgencyResource;
use App\Modules\Agency\Service\AgencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="Agency",
 *     type="object",
 *     title="Agency",
 *     description="Agencias ejecutoras que implementan programas y proyectos",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único de la agencia"),
 *     @OA\Property(property="name", type="string", example="UNICEF Bolivia", description="Nombre de la agencia (único)"),
 *     @OA\Property(property="url", type="string", format="url", example="https://www.unicef.org/bolivia", description="Sitio web de la agencia"),
 *     @OA\Property(property="is_approved", type="boolean", example=true, description="Estado de aprobación de la agencia")
 * )
 */
class AgencyController extends Controller
{
    private AgencyService $agencyService;

    public function __construct(AgencyService $agencyService)
    {
        $this->agencyService = $agencyService;
    }

    /**
     * @OA\Get(
     *     path="/agencies",
     *     tags={"Agencies"},
     *     summary="Listar todas las agencias",
     *     description="Obtiene la lista completa de agencias ejecutoras registradas en el sistema",
     *     @OA\Response(
     *         response=200,
     *         description="Lista obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de agencias obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="agencies",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Agency")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=12)
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
            $perPage = (int) $request->get("per_page", 10);

            $agencies = $this->agencyService->getAllAgencies($perPage);
            
            return ApiResponse::success(
                'Agencies paginated list successfully uploaded',
                200,
                [
                    'agencies' => AgencyResource::collection($agencies),
                    'total' => $agencies->count(),
                    'per_page' => $agencies->perPage(),
                    'current_page' => $agencies->currentPage(),
                    'last_page' => $agencies->lastPage(),
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
     *     path="/agencies/{id}",
     *     tags={"Agencies"},
     *     summary="Obtener agencia específica",
     *     description="Obtiene la información detallada de una agencia ejecutora por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la agencia",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Agencia encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Agencia encontrada"),
     *             @OA\Property(property="data", ref="#/components/schemas/Agency")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Agencia no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Agencia no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $agency = $this->agencyService->getAgencyById($id);

            return ApiResponse::success(
                'Agency Found',
                200,
                new AgencyResource($agency)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Agency');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/agencies",
     *     tags={"Agencies"},
     *     summary="Crear nueva agencia",
     *     description="Registra una nueva agencia ejecutora. El nombre debe ser único en el sistema.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "url", "is_approved"},
     *                 @OA\Property(property="name", type="string", maxLength=255, example="Plan International", description="Nombre de la agencia (requerido, único)"),
     *                 @OA\Property(property="url", type="string", format="url", maxLength=500, example="https://plan-international.org", description="Sitio web de la agencia (requerido)"),
     *                 @OA\Property(property="is_approved", type="boolean", example=true, description="Estado de aprobación (requerido)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Agencia creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Agencia creada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Agency")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="el nombre debe tener al menos 2 caracteres")
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
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="Ya existe una agencia con el nombre: Plan International")
     *                 ),
     *                 @OA\Property(
     *                     property="url",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo url es obligatorio.")
     *                 ),
     *                 @OA\Property(
     *                     property="is_approved",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo is_approved debe ser verdadero o falso.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(AgencyRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $agency = $this->agencyService->createAgency(
                $validated['name'],
                $validated['url'],
                $validated['is_approved']
            );

            return ApiResponse::created(
                'Agency created successfully',
                new AgencyResource($agency)
            );

        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @OA\Put(
     *     path="/agencies/{id}",
     *     tags={"Agencies"},
     *     summary="Actualizar agencia existente",
     *     description="Actualiza la información de una agencia ejecutora. El nombre debe ser único.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la agencia a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "url", "is_approved"},
     *                 @OA\Property(property="name", type="string", maxLength=255, example="UNICEF Estado Plurinacional de Bolivia", description="Nombre actualizado"),
     *                 @OA\Property(property="url", type="string", format="url", maxLength=500, example="https://www.unicef.org/bolivia/es", description="URL actualizada"),
     *                 @OA\Property(property="is_approved", type="boolean", example=false, description="Estado de aprobación actualizado")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Agencia actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Agencia actualizada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Agency")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Agencia no encontrada"
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
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="Ya existe una agencia con el nombre: Save the Children")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function update(AgencyRequest $request, int $id): JsonResponse
    {
        try {
            $validated =$request->validated();

            $agency = $this->agencyService->updateAgency(
                $id,
                $validated['name'],
                $validated['url'],
                $validated['is_approved']
            );

            return ApiResponse::success(
                'Agency updated successfully',
                200,
                new AgencyResource($agency)
            );

        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }


    /**
     * @OA\Get(
     *     path="/agencies/search",
     *     tags={"Agencies"},
     *     summary="Buscar agencia por nombre",
     *     description="Busca una agencia ejecutora específica por su nombre (búsqueda exacta, case-insensitive)",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Nombre de la agencia a buscar",
     *         @OA\Schema(type="string", example="UNICEF Bolivia")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Agencia encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Agencia encontrada"),
     *             @OA\Property(property="data", ref="#/components/schemas/Agency")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Agencia no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Agencia no encontrado")
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
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo name es obligatorio.")
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
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $agency = $this->agencyService->findAgencyByName($request->input('name'));

            return ApiResponse::success(
                'Agency found',
                200,
                new AgencyResource($agency)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Agency');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function getAgenciesExcluding(Request $request): JsonResponse{
        try{
            $search = $request->get("search");
            $perPage = (int) $request->get("per_page", 10);
            $exclude = (array) $request->input('exclude', []);

            $agencies = $this->agencyService->getAgenciesExcluding($perPage, $search, $exclude);

            return ApiResponse::success(
                'Agencies list successfully obtained',
                200,
                [
                    'agencies' => AgencyResource::collection($agencies),
                    'total' => $agencies->count(),
                    'per_page' => $agencies->perPage(),
                    'current_page' => $agencies->currentPage(),
                    'last_page' => $agencies->lastPage(),
                    'all' => $agencies->total(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
