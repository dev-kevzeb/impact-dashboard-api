<?php

namespace App\Modules\IndicatorType\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndicatorTypeRequest;
use App\Http\Resources\IndicatorTypeResource;
use App\Http\Responses\ApiResponse;
use App\Modules\IndicatorType\Domain\IndicatorType;
use App\Modules\IndicatorType\Service\IndicatorTypeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="IndicatorType",
 *     type="object",
 *     title="IndicatorType",
 *     description="Tipos de indicadores (Cuantitativo, Cualitativo, etc.) que clasifican los indicadores de desempeño",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del tipo de indicador"),
 *     @OA\Property(property="name", type="string", example="Cuantitativo", description="Nombre del tipo de indicador (único)")
 * )
 */
class IndicatorTypeController extends Controller
{
    private IndicatorTypeService $indicatorTypeService;

    public function __construct(IndicatorTypeService $indicatorTypeService)
    {
        $this->indicatorTypeService = $indicatorTypeService;
    }

    /**
     * @OA\Get(
     *     path="/indicator_types",
     *     tags={"Indicator Types"},
     *     summary="Listar todos los tipos de indicador",
     *     description="Obtiene la lista completa de tipos de indicadores disponibles en el sistema (Cuantitativo, Cualitativo, etc.)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de tipos de indicator obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="indicatorTypes",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/IndicatorType")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=4)
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

            $indicatorTypes = $this->indicatorTypeService->getPaginatedIndicatorTypes($perPage, $search);

            return ApiResponse::success(
                "Indicator Types paginated list successfully uploaded",
                200,
                [
                    'indicator_types' => IndicatorTypeResource::collection($indicatorTypes),
                    'total' => $indicatorTypes->count(),
                    'per_page' => $indicatorTypes->perPage(),
                    'current_page' => $indicatorTypes->currentPage(),
                    'last_page' => $indicatorTypes->lastPage(),
                ]
            );
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), 500);
        } catch (\Exception $exception) {
            return ApiResponse::error($exception->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/indicator_types",
     *     tags={"Indicator Types"},
     *     summary="Crear nuevo tipo de indicador",
     *     description="Registra un nuevo tipo de indicador. El nombre debe ser único en el sistema.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string", maxLength=255, example="Cualitativo", description="Nombre del tipo de indicador (requerido, único)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tipo de indicador creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tipo de indicador creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/IndicatorType")
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
     *                     @OA\Items(type="string", example="Ya existe un tipo de indicador con el nombre: Cualitativo")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(IndicatorTypeRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $isBottomUp = isset($validated['is_bottom_up']) ? (bool) $validated['is_bottom_up'] : true;
            $indicatorType = $this->indicatorTypeService->createIndicatorType($validated['name'], $isBottomUp);
            return ApiResponse::created(
                'Indicator Type created successfully',
                new IndicatorTypeResource($indicatorType),

            );
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/indicator_types/{id}",
     *     tags={"Indicator Types"},
     *     summary="Obtener tipo de indicador específico",
     *     description="Obtiene la información detallada de un tipo de indicador por su ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del tipo de indicador",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tipo de indicador encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tipo de indicador encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/IndicatorType")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tipo de indicador no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tipo de indicador no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $indicatorType = $this->indicatorTypeService->getIndicatorTypeById($id);
            return ApiResponse::success(
                'Indicator Type found',
                200,
                new IndicatorTypeResource($indicatorType),
            );
        } catch (RuntimeException $exception) {
            return ApiResponse::notFound('Indicator Type');
        } catch (\Exception $exception) {
            return ApiResponse::error($exception->getMessage(), 400);
        }
    }

    /**
     * @OA\Put(
     *     path="/indicator_types/{id}",
     *     tags={"Indicator Types"},
     *     summary="Actualizar tipo de indicador existente",
     *     description="Actualiza el nombre de un tipo de indicador. El nombre debe ser único en el sistema.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del tipo de indicador a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string", maxLength=255, example="Mixto", description="Nombre actualizado del tipo (debe ser único)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tipo de indicador actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tipo de indicator actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/IndicatorType")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tipo de indicador no encontrado"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio"
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
     *                     @OA\Items(type="string", example="Ya existe un tipo de indicador con el nombre: Mixto")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function update(IndicatorTypeRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $isBottomUp = isset($validated['is_bottom_up']) ? (bool) $validated['is_bottom_up'] : true;

            $indicatorType = $this->indicatorTypeService->updateIndicatorType($id, $validated['name'], $isBottomUp);

            return ApiResponse::success(
                'Indicator Type uploaded successfully',
                200,
                new IndicatorTypeResource($indicatorType),
            );
        } catch (RuntimeException $exception) {
            return ApiResponse::notFound('Indicator Type');
        } catch (\Exception $exception) {
            return ApiResponse::error($exception->getMessage(), 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/indicator_types/search",
     *     tags={"Indicator Types"},
     *     summary="Buscar tipo de indicador por nombre",
     *     description="Busca un tipo de indicador específico por su nombre (búsqueda exacta, case-insensitive)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Nombre del tipo de indicador a buscar",
     *         @OA\Schema(type="string", example="Cuantitativo")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tipo de indicador encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tipo de indicador encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/IndicatorType")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tipo de indicador no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tipo de Indicador no encontrado")
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
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $indicatorType = $this->indicatorTypeService->findIndicatorTypeByName($request->input('name'));

            return ApiResponse::success(
                'Indicator Type found',
                200,
                new IndicatorTypeResource($indicatorType),
            );
        } catch (RuntimeException $exception) {
            return ApiResponse::notFound('Indicator Type');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $exception) {
            return ApiResponse::error($exception->getMessage(), 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/indicator-types/{id}",
     *     tags={"Indicator Types"},
     *     summary="Eliminar tipo de indicador",
     *     description="Elimina un tipo de indicador. No se puede eliminar si está asignado a indicadores existentes.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del tipo de indicador a eliminar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Tipo de indicador eliminado exitosamente",
     *         @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Indicator Type deleted successfully"))
     *     ),
     *     @OA\Response(response=400, description="Error: tipo asignado a indicadores o no encontrado"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->indicatorTypeService->deleteIndicatorType($id);
            return ApiResponse::success('Indicator Type deleted successfully', 200);
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
