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
 *     description="Measures (outcome indicators) associated with strategic outputs, with their performance indicators",
 *     @OA\Property(property="id", type="integer", example=1, description="Unique measure ID"),
 *     @OA\Property(property="name", type="string", example="Number of improved schools", description="Measure name (2-100 characters)"),
 *     @OA\Property(
 *         property="indicators",
 *         type="array",
 *        description="Performance indicators associated with the measure (optional, loaded with load)",
 *        @OA\Items(
 *            type="object",
 *           @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Schools with renovated infrastructure"),
 *             @OA\Property(property="target", type="number", format="float", example=50),
 *             @OA\Property(property="type_id", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Property(property="indicators_count", type="integer", example=3, description="Number of associated indicators (optional)")
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
     *     summary="List all measures",
     *     description="Retrieves the complete list of measures (outcome indicators) without their associated indicators",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Measure list retrieved successfully"),
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
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $measure = $this->measureService->getAllMeasures();

            return ApiResponse::success(
                'List of measure successfully obtained',
                200,
                [
                    'measures' => MeasureResource::collection($measure),
                    'total' => $measure->count(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal Server Error', 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/measures/{id}",
     *     tags={"Measures"},
     *     summary="Get specific measure",
     *     description="Retrieves details of a measure by its ID, without its associated indicators",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Measure ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Measure found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Measure found"),
     *             @OA\Property(property="data", ref="#/components/schemas/Measure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Measure not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Measure not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function show(int $id)
    {
        try {
            $measure = $this->measureService->getMeasureById($id);

            return ApiResponse::success(
                'Measure found',
                200,
                new MeasureResource($measure)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Measure');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/measures/{id}/with-indicators",
     *     tags={"Measures"},
     *     summary="Get measure with its indicators",
     *     description="Retrieves a measure with all its associated performance indicators (eager loading)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Measure ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Measure with indicators retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Measure with its indicators retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Measure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Measure not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function showWithIndicators(int $id)
    {
        try {
            $measure = $this->measureService->getMeasureById($id);

            $measure->load('indicators');

            return ApiResponse::success(
                'Measure with its indicators successfully recovered',
                200,
                new MeasureResource($measure)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Measure');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/measures/{id}/with-indicators-count",
     *     tags={"Measures"},
     *     summary="Get measure with indicator count",
     *     description="Retrieves a measure with its indicators and the total count of associated indicators",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Measure ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Measure with indicators and count retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Measure list with their indicators retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Measure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Measure not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function showWithIndicatorsCount(int $id)
    {
        try {
            $measure = $this->measureService->getMeasureById($id);
            $measure->load('indicators');

            return ApiResponse::success(
                'List of measures with their indicators successfully recovered',
                200,
                new MeasureResource($measure)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Measure');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server Error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/measures",
     *     tags={"Measures"},
     *     summary="Create new measure",
     *     description="Registers a new measure associated with a specific strategic output",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "strategic_output_id"},
     *                 @OA\Property(property="name", type="string", minLength=2, maxLength=100, example="Number of direct beneficiaries", description="Measure name (required, 2-100 characters)"),
     *                 @OA\Property(property="strategic_output_id", type="integer", example=1, description="Strategic output ID (required, must exist)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Measure created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Measure created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Measure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Strategic output not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Measure not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Technical validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
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
                'Measure created successfully',
                new MeasureResource($measure)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Measure');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/measures/{id}",
     *     tags={"Measures"},
     *     summary="Update existing measure",
     *     description="Updates the name and/or associated strategic output of a measure",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Measure ID to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "strategic_output_id"},
     *            @OA\Property(property="name", type="string", minLength=2, maxLength=100, example="Number of indirect beneficiaries", description="Updated name"),
     *            @OA\Property(property="strategic_output_id", type="integer", example=2, description="Updated strategic output ID")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Measure updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Measure updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Measure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Domain validation error"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Technical validation error"
     *     )
     * )
     */
    public function update(MeasureRequest $request, int $id)
    {
        try {

            $validated = $request->validated();

            $measure = $this->measureService->updateMeasure($id, $validated['name'], $validated['strategic_output_id']);

            return ApiResponse::success(
                'Measure uploaded successfully',
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
     *     summary="Search indicator by name in measure",
     *     description="Retrieves a specific indicator from a measure by searching for its name",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Measure ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="indicatorName",
     *         in="path",
     *         required=true,
     *         description="Indicator name to search",
     *         @OA\Schema(type="string", example="Renovated schools")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Indicator found successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Indicator found successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="indicator", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Technical validation error"
     *     )
     * )
     */
    public function getIndicatorByName(int $id, string $indicatorName)
    {
        try {
            $indicator = $this->measureService->getIndicatorOfMeasureByName($id, $indicatorName);

            return ApiResponse::success(
                'Indicator found successfully',
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
     *     summary="Add indicator to measure",
     *     description="Associates a new performance indicator with a specific measure",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "measure_id", "target", "type_id"},
     *                 @OA\Property(property="name", type="string", minLength=2, maxLength=100, example="Completion percentage", description="Indicator name (required, 2-100 characters)"),
     *                 @OA\Property(property="measure_id", type="integer", example=1, description="Measure ID (required)"),
     *                 @OA\Property(property="target", type="number", format="float", example=75.5, description="Numeric target of the indicator (required)"),
     *                 @OA\Property(property="type_id", type="integer", example=1, description="Indicator type ID (required, must exist in indicator_type)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Indicator added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Indicator added to measure successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Measure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Domain validation error (duplicate indicator)"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Technical validation error"
     *     )
     * )
     */
    public function addIndicator(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|min:2|max:100',
                'measure_id' => 'required|integer',
                'target' => 'required|numeric',
                'type_id' => 'required|integer'
            ]);

            $measure = $this->measureService->getMeasureById($request->input('measure_id'));
            $indicatorType = $this->indicatorTypeService->getIndicatorTypeById($request->input('type_id'));

            $indicator = Indicator::at($request->input('name'), $indicatorType, $request->input('target'), $measure);
            $measure->addIndicator($indicator);
            $measure->load('indicators');

            return ApiResponse::success(
                'Indicator successfully added to the measure',
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
     *     summary="Remove indicator from measure",
     *     description="Removes a performance indicator associated with a specific measure",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"measure_id", "indicator_id"},
     *                 @OA\Property(property="measure_id", type="integer", example=1, description="Measure ID (required)"),
     *                 @OA\Property(property="indicator_id", type="integer", example=5, description="Indicator ID to remove (required)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Indicator successfully removed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Indicator successfully removed"),
     *             @OA\Property(property="data", ref="#/components/schemas/Measure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Technical validation error"
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
            'Indicator successfully removed',
            200,
            new MeasureResource($measure)
        );
    }

    /**
     * @OA\Get(
     *     path="/measures/search",
     *     tags={"Measures"},
     *     summary="Search measure by name",
     *     description="Searches for a specific measure by its name (exact search, case-insensitive)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Measure name to search",
     *         @OA\Schema(type="string", example="Number of improved schools")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Measure found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Measure found"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="measure", ref="#/components/schemas/Measure")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Technical validation error"
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $measure = $this->measureService->findMeasureByName($request->input('name'));

            return ApiResponse::success(
                'Measure found',
                200,
                ['measure' => new MeasureResource($measure)]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    public function listByStrategicOutput(int $id)
    {
        try {
            $measures = $this->measureService->getAllMeasuresByStrategicOutputId($id);

            return ApiResponse::success(
                'Measure obtained correctly',
                200,
                MeasureResource::collection($measures)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal Server Error', 500);
        }
    }

    public function measuresListByStrategicOutput(Request $request, int $id)
    {
        try {
            $search = $request->get("search");
            $perPage = (int) $request->get("per_page", 10);

            $measures = $this->measureService->getMeasuresByStrategicOutputId($id, $perPage, $search);

            return ApiResponse::success(
                'Measures paginated list successfully uploaded',
                200,
                [
                    'measures' => MeasureResource::collection($measures),
                    'total' => $measures->count(),
                    'per_page' => $measures->perPage(),
                    'current_page' => $measures->currentPage(),
                    'last_page' => $measures->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
