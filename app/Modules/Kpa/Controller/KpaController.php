<?php

namespace App\Modules\Kpa\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\KpaRequest;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\KpaResource;
use App\Modules\Kpa\Service\KpaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="Kpa",
 *     type="object",
 *     title="Kpa",
 *     description="Áreas Clave Prioritarias que definen los focos estratégicos de los programas y proyectos",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del KPA"),
 *     @OA\Property(property="name", type="string", example="Educación de Calidad", description="Nombre del área prioritaria"),
 *     @OA\Property(property="implementation", type="number", format="float", example=75.5, description="Porcentaje de implementación (0-100)")
 * )
 */
class KpaController extends Controller
{
    private KpaService $kpaService;

    public function __construct(KpaService $kpaService)
    {
        $this->kpaService = $kpaService;
    }

    /**
     * @OA\Get(
     *     path="/kpas",
     *     tags={"KPAs"},
     *     summary="Listar todos los KPAs",
     *     description="Obtiene la lista completa de Áreas Clave Prioritarias (Key Priority Areas) con su nivel de implementación",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de KPAs obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="kpas",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Kpa")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=6, description="Total de KPAs registrados")
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

            $kpas = $this->kpaService->getKpasPaginated($search, $perPage);

            return ApiResponse::success(
                'KPAs paginated list successfully uploaded',
                200,
                [
                    'kpas' => KpaResource::collection($kpas),
                    'total' => $kpas->count(),
                    'per_page' => $kpas->perPage(),
                    'current_page' => $kpas->currentPage(),
                    'last_page' => $kpas->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/kpas/{id}",
     *     tags={"KPAs"},
     *     summary="Obtener un KPA específico",
     *     description="Obtiene la información detallada de un Área Clave Prioritaria por su ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del KPA a obtener",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="KPA encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="KPA encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Kpa")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="KPA no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="KPA no encontrado")
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
            $kpa = $this->kpaService->getKpaById($id);

            return ApiResponse::success(
                'KPA found',
                200,
                new KpaResource($kpa)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('KPA');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/kpas",
     *     tags={"KPAs"},
     *     summary="Crear nuevo KPA",
     *     description="Registra una nueva Área Clave Prioritaria con su porcentaje de implementación inicial.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "implementation"},
     *                 @OA\Property(property="name", type="string", maxLength=255, example="Salud y Bienestar", description="Nombre del área prioritaria (requerido)"),
     *                 @OA\Property(property="implementation", type="number", format="float", example=50.0, description="Porcentaje de implementación inicial (0-100, requerido)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="KPA creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="KPA creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Kpa")
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
     *                     @OA\Items(type="string", example="El campo name es obligatorio.")
     *                 ),
     *                 @OA\Property(
     *                     property="implementation",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo implementation debe ser numérico.")
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
    public function store(KpaRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $kpa = $this->kpaService->createKpa(
                $validated['name'],
                (float) $validated['implementation']
            );

            return ApiResponse::created(
                'KPA created successfully',
                new KpaResource($kpa)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }


    /**
     * @OA\Put(
     *     path="/kpas/{id}",
     *     tags={"KPAs"},
     *     summary="Actualizar KPA existente",
     *     description="Actualiza la información de un Área Clave Prioritaria, incluyendo su porcentaje de implementación.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del KPA a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "implementation"},
     *                 @OA\Property(property="name", type="string", maxLength=255, example="Educación de Calidad e Inclusiva", description="Nombre actualizado del área prioritaria"),
     *                 @OA\Property(property="implementation", type="number", format="float", example=85.5, description="Porcentaje de implementación actualizado (0-100)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="KPA actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="KPA actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Kpa")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="KPA no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="KPA no encontrado")
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
     *                     property="implementation",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo implementation debe ser numérico.")
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
    public function update(KpaRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $kpa = $this->kpaService->updateKpa(
                $id,
                $validated['name'],
                (float) $validated['implementation']
            );

            return ApiResponse::success(
                'KPA uploaded successfully',
                200,
                new KpaResource($kpa)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @OA\Get(
     *     path="/kpas/search",
     *     tags={"KPAs"},
     *     summary="Buscar KPA por nombre",
     *     description="Busca un Área Clave Prioritaria específica por su nombre (búsqueda exacta, case-insensitive)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Nombre del KPA a buscar",
     *         @OA\Schema(type="string", example="Educación de Calidad")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="KPA encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="KPA encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Kpa")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="KPA no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="KPA no encontrado")
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

            $kpa = $this->kpaService->findKpaByName($request->input('name'));

            return ApiResponse::success(
                'KPA found',
                200,
                new KpaResource($kpa)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('KPA');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
