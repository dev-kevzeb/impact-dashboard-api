<?php
namespace App\Modules\StrategicOutput\Controller;

use App\Http\Requests\StrategicOutputRequest;
use App\Http\Resources\StrategicOutputResource;
use App\Modules\Measure\Domain\Measure;
use App\Modules\Measure\Service\MeasureService;
use App\Modules\StrategicOutput\Service\StrategicOutputService;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="StrategicOutput",
 *     type="object",
 *     title="StrategicOutput",
 *     description="Resultados estratégicos esperados de los programas, vinculados a un Country-KPA y con medidas asociadas",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del resultado estratégico"),
 *     @OA\Property(property="name", type="string", example="Mejorar la calidad educativa en zonas rurales", description="Nombre del resultado estratégico (max 200 caracteres)"),
 *     @OA\Property(property="id_ck", type="integer", example=1, description="ID de la relación Country-KPA (FK a country_kpa)"),
 *     @OA\Property(
 *         property="countryKpa",
 *         type="object",
 *         description="Relación Country-KPA asociada",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(
 *             property="country",
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Bolivia")
 *         ),
 *         @OA\Property(
 *             property="kpa",
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=2),
 *             @OA\Property(property="name", type="string", example="Educación de Calidad")
 *         )
 *     ),
 *     @OA\Property(
 *         property="measures",
 *         type="array",
 *         description="Medidas asociadas al resultado estratégico",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Número de escuelas mejoradas")
 *         )
 *     )
 * )
 */
class StrategicOutputController extends Controller
{
    private StrategicOutputService $strategicOutputService;
    private MeasureService $measureService;

    public function __construct(StrategicOutputService $strategicOutputService, MeasureService $measureService)
    {
        $this->strategicOutputService = $strategicOutputService;
        $this->measureService = $measureService;
    }

    /**
     * @OA\Get(
     *     path="/strategic_outputs",
     *     tags={"Strategic Outputs"},
     *     summary="Listar todos los resultados estratégicos",
     *     description="Obtiene la lista completa de resultados estratégicos con sus relaciones: Country-KPA, país, KPA y medidas",
     *     @OA\Response(
     *         response=200,
     *         description="Lista obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de resultados estratégicos obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="strategic_outputs",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/StrategicOutput")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=10, description="Total de resultados estratégicos")
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
            $strategicOutputs = $this->strategicOutputService->getAllStrategicOutputs();
            $strategicOutputs->load(['countryKpa.country', 'countryKpa.kpa', 'measures']);

            return ApiResponse::success(
                'Lista de resultados estratégicos obtenida exitosamente',
                200,
                [
                    'strategic_outputs' => StrategicOutputResource::collection($strategicOutputs),
                    'total' => $strategicOutputs->count()
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
     *     path="/strategic_outputs/{id}",
     *     tags={"Strategic Outputs"},
     *     summary="Obtener resultado estratégico específico",
     *     description="Obtiene el detalle de un resultado estratégico por su ID, incluyendo Country-KPA, país, KPA y medidas",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del resultado estratégico",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resultado estratégico encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Resultado estratégico encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/StrategicOutput")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resultado estratégico no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Resultado estratégico no encontrado")
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
            $strategicOutput = $this->strategicOutputService->getStrategicOutputById($id);
            
            $strategicOutput->load(['measures']);
            
            return ApiResponse::success(
                'Resultado estratégico encontrado',
                200,
                new StrategicOutputResource($strategicOutput)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Resultado estratégico');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function showByCountryKpa($id)
    {
        try {
            $strategicOutputs = $this->strategicOutputService->getByCountryKpaId($id);

            return ApiResponse::success(
                'Resultados estratégicos para CountryKpa obtenidos correctamente',
                200,
                StrategicOutputResource::collection($strategicOutputs)
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }


    public function store(StrategicOutputRequest $request)
    {
        $validated = $request->validated();

        try {
            $strategicOutput = $this->strategicOutputService->createStrategicOutput(
                $validated['name'],
                $validated['id_ck']
            );
            
            $strategicOutput->load(['countryKpa.country', 'countryKpa.kpa', 'measures']);
            
            return ApiResponse::created(
                'Resultado estratégico creado exitosamente',
                new StrategicOutputResource($strategicOutput)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/strategic_outputs/{id}",
     *     tags={"Strategic Outputs"},
     *     summary="Actualizar resultado estratégico",
     *     description="Actualiza un resultado estratégico existente, incluyendo su nombre y/o Country-KPA asociado",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del resultado estratégico a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string", maxLength=200, example="Mejorar la cobertura educativa en 20%", description="Nombre actualizado (requerido)"),
     *                 @OA\Property(property="id_ck", type="integer", example=2, description="ID del Country-KPA actualizado (opcional, debe existir en country_kpa)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resultado estratégico actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Resultado estratégico actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/StrategicOutput")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resultado estratégico no encontrado"
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
    public function update(StrategicOutputRequest $request, $id)
    {
        $validated = $request->validated();

        try {
            $strategicOutput = $this->strategicOutputService->updateStrategicOutput(
                $id,
                $validated['name'],
                $validated['id_ck'] ?? null
            );
            
            $strategicOutput->load(['countryKpa.country', 'countryKpa.kpa', 'measures']);
            
            return ApiResponse::success(
                'Resultado estratégico actualizado exitosamente',
                200,
                new StrategicOutputResource($strategicOutput)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/strategic_outputs/add_measure",
     *     tags={"Strategic Outputs"},
     *     summary="Agregar medida a resultado estratégico",
     *     description="Asocia una nueva medida (indicador) a un resultado estratégico específico",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"strategic_output_id", "name"},
     *                 @OA\Property(property="strategic_output_id", type="integer", example=1, description="ID del resultado estratégico (requerido)"),
     *                 @OA\Property(property="name", type="string", minLength=2, maxLength=100, example="Número de docentes capacitados", description="Nombre de la medida (requerido, 2-100 caracteres)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Medida agregada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Medida agregada exitosamente al resultado estratégico"),
     *             @OA\Property(property="data", ref="#/components/schemas/StrategicOutput")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="El nombre de la medida debe tener al menos 2 caracteres")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación técnica"
     *     )
     * )
     */
    public function addMeasure(Request $request)
    {
        try {
            $request->validate([
                'strategic_output_id' => 'required|integer',
                'name' => 'required|string|min:2|max:100',
            ]);

            $strategicOutput = $this->strategicOutputService->getStrategicOutputById($request->input('strategic_output_id'));

            $measure = Measure::at($request->input('name'), $strategicOutput);
            $strategicOutput->addMeasure($measure);

            $strategicOutput->load('measures');

            return ApiResponse::success(
                'Medida agregada exitosamente al resultado estratégico',
                200,
                $strategicOutput
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @OA\Post(
     *     path="/strategic_outputs/remove_measure",
     *     tags={"Strategic Outputs"},
     *     summary="Remover medida de resultado estratégico",
     *     description="Elimina una medida (indicador) asociada a un resultado estratégico específico",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"strategic_output_id", "measure_id"},
     *                 @OA\Property(property="strategic_output_id", type="integer", example=1, description="ID del resultado estratégico (requerido)"),
     *                 @OA\Property(property="measure_id", type="integer", example=5, description="ID de la medida a eliminar (requerido)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Medida removida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Medida removida exitosamente del resultado estratégico"),
     *             @OA\Property(property="data", ref="#/components/schemas/StrategicOutput")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Resultado estratégico o medida no encontrados")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación técnica"
     *     )
     * )
     */
    public function removeMeasure(Request $request)
    {
        try {
            $request->validate([
                'strategic_output_id' => 'required|integer',
                'measure_id' => 'required|integer'
            ]);

            $strategicOutput = $this->strategicOutputService
                ->getStrategicOutputById($request->input('strategic_output_id'));

            $measure = $this->measureService->getMeasureById($request->input('measure_id'));
            if ($measure->strategic_output_id !== $strategicOutput->id) throw new RuntimeException('La medida no pertenece a este resultado estratégico');

            $measure->delete();

            $strategicOutput->load('measures');

            return ApiResponse::success(
                'Medida removida exitosamente del resultado estratégico',
                200,
                $strategicOutput
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }
    /**
     * @OA\Get(
     *     path="/strategic_outputs/search",
     *     tags={"Strategic Outputs"},
     *     summary="Buscar resultado estratégico por nombre",
     *     description="Busca un resultado estratégico específico por su nombre (búsqueda exacta, case-insensitive) con relaciones completas",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Nombre del resultado estratégico a buscar",
     *         @OA\Schema(type="string", example="Mejorar la calidad educativa")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resultado estratégico encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Resultado estratégico encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/StrategicOutput")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación o no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Resultado estratégico no encontrado")
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
     *     )
     * )
     */
    public function search(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $strategicOutput = $this->strategicOutputService
                ->findStrategicOutputByName($request->input('name'));

            $strategicOutput->load(['countryKpa.country', 'countryKpa.kpa', 'measures']);

            return ApiResponse::success(
                'Resultado estratégico encontrado',
                200,
                new StrategicOutputResource($strategicOutput)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }



}
