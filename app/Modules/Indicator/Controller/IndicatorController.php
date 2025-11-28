<?php

namespace App\Modules\Indicator\Controller;

use App\Http\Controllers\Controller;
use App\Http\Resources\IndicatorResource;
use App\Http\Responses\ApiResponse;

use App\Modules\Indicator\Service\IndicatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="Indicator",
 *     type="object",
 *     title="Indicador",
 *     description="Indicadores de desempeño asociados a medidas, con su tipo y meta numérica",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del indicador"),
 *     @OA\Property(property="name", type="string", example="Porcentaje de cumplimiento de metas", description="Nombre del indicador"),
 *     @OA\Property(property="target", type="number", format="float", example=85.5, description="Meta numérica del indicador"),
 *     @OA\Property(
 *         property="type",
 *         type="object",
 *         description="Tipo de indicador",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Cuantitativo")
 *     )
 * )
 */
class IndicatorController extends Controller
{
    private IndicatorService $indicatorService;

    public function __construct(IndicatorService $indicatorService)
    {
        $this->indicatorService = $indicatorService;
    }

    /**
     * @OA\Get(
     *     path="/indicators",
     *     tags={"Indicators"},
     *     summary="Listar todos los indicadores",
     *     description="Obtiene la lista completa de indicadores de desempeño con sus tipos asociados",
     *     @OA\Response(
     *         response=200,
     *         description="Lista obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de Indicadores obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="indicators",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Indicator")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=30)
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
    public function index(): JsonResponse
    {
        try{
            $indicators = $this->indicatorService->getAllIndicators();

            return ApiResponse::success(
                'Lista de Indicadores obtenida exitosamente',
                200,
                [
                    'indicators' => IndicatorResource::collection($indicators),
                    'total' => $indicators->count(),
                ]
            );
        }  catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/indicators/{id}",
     *     tags={"Indicators"},
     *     summary="Obtener indicador específico",
     *     description="Obtiene el detalle de un indicador de desempeño por su ID, incluyendo su tipo",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del indicador",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Indicador encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Indicador encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Indicator")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Indicador no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Indicador no encontrado")
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
        try{
            $indicator = $this->indicatorService->getIndicatorById($id);

            return ApiResponse::success(
                'Indicador encontrado',
                200,
                new IndicatorResource($indicator)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Indicador');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/indicators",
     *     tags={"Indicators"},
     *     summary="Crear nuevo indicador",
     *     description="Registra un nuevo indicador de desempeño asociado a una medida específica",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "target", "type_id", "measure_id"},
     *                 @OA\Property(property="name", type="string", example="Número de docentes capacitados anualmente", description="Nombre del indicador (requerido)"),
     *                 @OA\Property(property="target", type="number", format="float", example=100.0, description="Meta numérica del indicador (requerido)"),
     *                 @OA\Property(property="type_id", type="integer", example=1, description="ID del tipo de indicador (requerido, debe existir en indicator_type)"),
     *                 @OA\Property(property="measure_id", type="integer", example=1, description="ID de la medida asociada (requerido, debe existir en measure)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Indicador creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Indicador creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Indicator")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="El tipo de indicador o medida no existen")
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
     *                     property="target",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo target debe ser numérico.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try{
            $request->validate([
                'name' => 'required|string',
                'target'=>'required|numeric',
                'type_id' => 'required|integer',
                'measure_id' => 'required|integer'
            ]);

            $indicator = $this->indicatorService->createIndicator(
                $request->input('name'),
                $request->input('target'),
                $request->input('type_id'),
                $request->input('measure_id'),
            );

            return ApiResponse::created(
                'Indicador creado exitosamente',
                new IndicatorResource($indicator)
            );

        }catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @OA\Put(
     *     path="/indicators/{id}",
     *     tags={"Indicators"},
     *     summary="Actualizar indicador existente",
     *     description="Actualiza la información de un indicador de desempeño, incluyendo nombre, meta, tipo y medida asociada",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del indicador a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "target", "type_id", "measure_id"},
     *                 @OA\Property(property="name", type="string", example="Número de docentes certificados anualmente", description="Nombre actualizado del indicador"),
     *                 @OA\Property(property="target", type="number", format="float", example=120.0, description="Meta numérica actualizada"),
     *                 @OA\Property(property="type_id", type="integer", example=2, description="ID del tipo de indicador actualizado"),
     *                 @OA\Property(property="measure_id", type="integer", example=1, description="ID de la medida asociada actualizada")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Indicador actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Indicador actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Indicator")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Indicador no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación técnica"
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try{
            $request->validate([
                'name' => 'required|string',
                'target'=>'required|numeric',
                'type_id' => 'required|integer',
                'measure_id' => 'required|integer'
            ]);

            $indicator = $this->indicatorService->updateIndicator(
                $id,
                $request->input('name'),
                $request->input('target'),
                $request->input('type_id'),
                $request->input('measure_id'),
            );

            return ApiResponse::success(
                    'Indicador actualizado exitosamente',
                    200,
                    new IndicatorResource($indicator)
                );

        }catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @OA\Get(
     *     path="/indicators/search",
     *     tags={"Indicators"},
     *     summary="Buscar indicador por nombre",
     *     description="Busca un indicador de desempeño específico por su nombre (búsqueda exacta, case-insensitive)",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Nombre del indicador a buscar",
     *         @OA\Schema(type="string", example="Porcentaje de cumplimiento")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Indicador encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Indicador encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Indicator")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Indicador no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Indicador no encontrado")
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
        try{
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $indicador = $this->indicatorService->findIndicatorByName($request->input('name'));

            return ApiResponse::success(
                'Indicador encontrado',
                200,
                new IndicatorResource($indicador)
            );
        }catch (RuntimeException $e) {
            return ApiResponse::notFound('Indicador');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }
}