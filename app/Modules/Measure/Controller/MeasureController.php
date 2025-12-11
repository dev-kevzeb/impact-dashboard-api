<?php

namespace App\Modules\Measure\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\MeasureRequest;
use App\Http\Resources\MeasureResource;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

use App\Modules\IndicatorType\Service\IndicatorTypeService;
use App\Modules\Indicator\Service\IndicatorService;
use App\Modules\Measure\Service\MeasureService;

use Illuminate\Http\Request;
use \App\Modules\Indicator\Domain\Indicator;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="Measure",
 *     type="object",
 *     title="Measure",
 *     description="Medidas (indicadores de resultado) asociadas a resultados estratégicos, con sus indicadores de desempeño",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único de la medida"),
 *     @OA\Property(property="name", type="string", example="Número de escuelas mejoradas", description="Nombre de la medida (2-100 caracteres)"),
 *     @OA\Property(
 *         property="indicators",
 *         type="array",
 *         description="Indicadores de desempeño asociados a la medida (opcional, cargado con load)",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Escuelas con infraestructura renovada"),
 *             @OA\Property(property="target", type="number", format="float", example=50),
 *             @OA\Property(property="type_id", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Property(property="indicators_count", type="integer", example=3, description="Cantidad de indicadores asociados (opcional)")
 * )
 */
class MeasureController extends Controller
{
    private MeasureService $measureService;
    private IndicatorTypeService $indicatorTypeService;
    private IndicatorService $indicatorService;

    public function __construct(MeasureService $measureService, IndicatorTypeService $indicatorTypeService, IndicatorService $indicatorService)
    {
        $this->measureService = $measureService;
        $this->indicatorTypeService = $indicatorTypeService;
        $this->indicatorService = $indicatorService;
    }

    /**
     * @OA\Get(
     *     path="/measures",
     *     tags={"Measures"},
     *     summary="Listar todas las medidas",
     *     description="Obtiene la lista completa de medidas (indicadores de resultado) sin sus indicadores asociados",
     *     @OA\Response(
     *         response=200,
     *         description="Lista obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de medidas obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="measures",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Measure")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=25)
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
        try{
            $measure = $this->measureService->getAllMeasures();

            return ApiResponse::success(
                'Lista de medidas obtenida exitosamente',
                200,
                [
                    'measures' => MeasureResource::collection($measure),
                    'total' => $measure->count(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function listByStrategicOutput(int $id)
    {
        try {
            $measures = $this->measureService->getMeasuresByStrategicOutputId($id);

            return ApiResponse::success(
                'Medidas obtenidas correctamente',
                200,
                MeasureResource::collection($measures)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/measures/{id}",
     *     tags={"Measures"},
     *     summary="Obtener medida específica",
     *     description="Obtiene el detalle de una medida por su ID, sin sus indicadores asociados",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la medida",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Medida encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Medida encontrada"),
     *             @OA\Property(property="data", ref="#/components/schemas/Measure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Medida no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Medida no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function show(int $id)
    {
        try {
            $measure = $this->measureService->getMeasureById($id);

            return ApiResponse::success(
                'Medida encontrada',
                200,
                new MeasureResource($measure)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Medida');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/measures/{id}/with-indicators",
     *     tags={"Measures"},
     *     summary="Obtener medida con sus indicadores",
     *     description="Obtiene una medida con todos sus indicadores de desempeño asociados (eager loading)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la medida",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Medida con indicadores recuperada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Medida con sus indicadores recuperada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Measure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Medida no encontrada"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function showWithIndicators(int $id)
    {
        try {
            $measure = $this->measureService->getMeasureById($id);

            $measure->load('indicators');

            return ApiResponse::success(
                'Medida con sus indicadores recuperada exitosamente',
                200,
                new MeasureResource($measure)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Medida');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/measures/{id}/with-indicators-count",
     *     tags={"Measures"},
     *     summary="Obtener medida con conteo de indicadores",
     *     description="Obtiene una medida con sus indicadores y el conteo total de indicadores asociados",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la medida",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Medida con indicadores y conteo recuperada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de medidas con sus indicadores recuperada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Measure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Medida no encontrada"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function showWithIndicatorsCount(int $id)
    {
        try {
            $measure = $this->measureService->getMeasureById($id);
            $measure->load('indicators');

            return ApiResponse::success(
                'Lista de medidas con sus indicadores recuperada exitosamente',
                200,
                new MeasureResource($measure)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Medida');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/measures",
     *     tags={"Measures"},
     *     summary="Crear nueva medida",
     *     description="Registra una nueva medida asociada a un resultado estratégico específico",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "strategic_output_id"},
     *                 @OA\Property(property="name", type="string", minLength=2, maxLength=100, example="Número de beneficiarios directos", description="Nombre de la medida (requerido, 2-100 caracteres)"),
     *                 @OA\Property(property="strategic_output_id", type="integer", example=1, description="ID del resultado estratégico (requerido, debe existir)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Medida creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Medida creada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Measure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resultado estratégico no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Medida no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación técnica"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function store(MeasureRequest $request)
    {
        try {
            $validated = $request->validated();

            $measure = $this->measureService->createMeasure(
                $validated['name'],
                $validated['strategic_output_id'],
                );


            return ApiResponse::created(
                'Medida creada exitosamente',
                new MeasureResource($measure)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Medida');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/measures/{id}",
     *     tags={"Measures"},
     *     summary="Actualizar medida existente",
     *     description="Actualiza el nombre y/o resultado estratégico asociado de una medida",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la medida a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "strategic_output_id"},
     *                 @OA\Property(property="name", type="string", minLength=2, maxLength=100, example="Número de beneficiarios indirectos", description="Nombre actualizado"),
     *                 @OA\Property(property="strategic_output_id", type="integer", example=2, description="ID del resultado estratégico actualizado")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Medida actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Medida actualziada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Measure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación técnica"
     *     )
     * )
     */
    public function update(MeasureRequest $request, int $id)
    {
        try {

            $validated = $request->validated();

            $measure = $this->measureService->updateMeasure($id, $validated['name'], $validated['strategic_output_id']);

            return ApiResponse::success(
                'Medida actualziada exitosamente',
                200,
                new MeasureResource($measure)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @OA\Get(
     *     path="/measures/{id}/indicator/{indicatorName}",
     *     tags={"Measures"},
     *     summary="Buscar indicador por nombre en medida",
     *     description="Obtiene un indicador específico de una medida buscando por su nombre",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la medida",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="indicatorName",
     *         in="path",
     *         required=true,
     *         description="Nombre del indicador a buscar",
     *         @OA\Schema(type="string", example="Escuelas renovadas")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Indicador encontrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Indicador encontrado exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="indicator", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación o no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación técnica"
     *     )
     * )
     */
    public function getIndicatorByName(int $id, string $indicatorName)
    {
        try {
            $indicator = $this->measureService->getIndicatorOfMeasureByName($id, $indicatorName);

            return ApiResponse::success(
                'Indicador encontrado exitosamente',
                200,
                ['indicator' => $indicator]
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @OA\Post(
     *     path="/measures/add-indicator",
     *     tags={"Measures"},
     *     summary="Agregar indicador a medida",
     *     description="Asocia un nuevo indicador de desempeño a una medida específica",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "measure_id", "target", "type_id"},
     *                 @OA\Property(property="name", type="string", minLength=2, maxLength=100, example="Porcentaje de cumplimiento", description="Nombre del indicador (requerido, 2-100 caracteres)"),
     *                 @OA\Property(property="measure_id", type="integer", example=1, description="ID de la medida (requerido)"),
     *                 @OA\Property(property="target", type="number", format="float", example=75.5, description="Meta numérica del indicador (requerido)"),
     *                 @OA\Property(property="type_id", type="integer", example=1, description="ID del tipo de indicador (requerido, debe existir en indicator_type)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Indicador agregado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Indicador agregado exitosamente a la medida"),
     *             @OA\Property(property="data", ref="#/components/schemas/Measure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación técnica"
     *     )
     * )
     */
    public function addIndicator(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|min:2|max:100',
                'measure_id' => 'required|integer',
                'target'=>'required|numeric',
                'type_id' => 'required|integer'
            ]);

            $measure = $this->measureService->getMeasureById($request->input('measure_id'));
            $indicatorType = $this->indicatorTypeService->getIndicatorTypeById($request->input('type_id'));

            $indicator = Indicator::at($request->input('name'), $indicatorType, $request->input('target'), $measure);
            $measure->addIndicator($indicator);
            $measure->load('indicators');

            return ApiResponse::success(
                'Indicador agregado exitosamente a la medida',
                200,
                new MeasureResource($measure)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @OA\Post(
     *     path="/measures/remove-indicator",
     *     tags={"Measures"},
     *     summary="Remover indicador de medida",
     *     description="Elimina un indicador de desempeño asociado a una medida específica",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"measure_id", "indicator_id"},
     *                 @OA\Property(property="measure_id", type="integer", example=1, description="ID de la medida (requerido)"),
     *                 @OA\Property(property="indicator_id", type="integer", example=5, description="ID del indicador a eliminar (requerido)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Indicador removido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Indicador removido exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Measure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación técnica"
     *     )
     * )
     */
    public function removeIndicator(Request $request)
    {
        $request->validate([
            'measure_id' => 'required|integer',
            'indicator_id' => 'required|integer'
        ]);

        $indicator = $this->indicatorService->getIndicatorById($request->input('indicator_id'));
        $this->measureService->removeIndicatorFromMeasure($request->input('measure_id'), $indicator);
        
        $measure = $this->measureService->getMeasureById($request->input('measure_id'));

        return ApiResponse::success(
            'Indicador removido exitosamente',
            200,
            new MeasureResource($measure)
        );
    }

    /**
     * @OA\Get(
     *     path="/measures/search",
     *     tags={"Measures"},
     *     summary="Buscar medida por nombre",
     *     description="Busca una medida específica por su nombre (búsqueda exacta, case-insensitive)",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Nombre de la medida a buscar",
     *         @OA\Schema(type="string", example="Número de escuelas mejoradas")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Medida encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Medida encontrada"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="measure", ref="#/components/schemas/Measure")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación o no encontrada"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación técnica"
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        try{
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $measure = $this->measureService->findMeasureByName($request->input('name'));

            return ApiResponse::success(
                'Medida encontrada',
                200,
                ['measure' => new MeasureResource($measure)]
            );

        }catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

}
