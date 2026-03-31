<?php

namespace App\Modules\Indicator\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndicatorRequest;
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
 *     title="Indicator",
 *    description="Performance indicators associated with measures, with their type and numeric target",
 *    @OA\Property(property="id", type="integer", example=1, description="Unique indicator ID"),
 *    @OA\Property(property="name", type="string", example="Percentage of target achievement", description="Indicator name"),
 *    @OA\Property(property="target", type="number", format="float", example=85.5, description="Numeric indicator target"),
 *     @OA\Property(
 *         property="type",
 *         type="object",
 *         description="Indicator type",
 *        @OA\Property(property="id", type="integer", example=1),
 *        @OA\Property(property="name", type="string", example="Quantitative")
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
     *     summary="List all indicators",
     *     description="Retrieves the complete list of performance indicators with their associated types",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Indicator list retrieved successfully"),
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
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $indicators = $this->indicatorService->getAllIndicators();

            return ApiResponse::success(
                'List of Indicators successfully obtained',
                200,
                [
                    'indicators' => IndicatorResource::collection($indicators),
                    'total' => $indicators->count(),
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
     *     path="/indicators/{id}",
     *     tags={"Indicators"},
     *     summary="Get specific indicator",
     *     description="Retrieves details of a performance indicator by its ID, including its type",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Indicator ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Indicator found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Indicator found"),
     *             @OA\Property(property="data", ref="#/components/schemas/Indicator")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Indicator not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Indicator not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $indicator = $this->indicatorService->getIndicatorById($id);

            return ApiResponse::success(
                'Indicator found',
                200,
                new IndicatorResource($indicator)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Indicator');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/indicators",
     *     tags={"Indicators"},
     *     summary="Create new indicator",
     *     description="Registers a new performance indicator associated with a specific measure",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "target", "type_id", "measure_id"},
     *           @OA\Property(property="name", type="string", example="Number of teachers trained annually", description="Indicator name (required)"),
     *            @OA\Property(property="target", type="number", format="float", example=100.0, description="Numeric indicator target (required)"),
     *            @OA\Property(property="type_id", type="integer", example=1, description="Indicator type ID (required, must exist in indicator_type)"),
     *            @OA\Property(property="measure_id", type="integer", example=1, description="Associated measure ID (required, must exist in measure)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Indicator created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Indicator created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Indicator")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Domain validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Indicator type or measure does not exist")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Technical validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
     *                 ),
     *                 @OA\Property(
     *                     property="target",
     *                     type="array",
     *                     @OA\Items(type="string", example="The target field must be numeric.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(IndicatorRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $indicator = $this->indicatorService->createIndicator(
                $validated['name'],
                $validated['target'],
                $validated['type_id'],
                $validated['measure_id'],
                $validated['actual_value'] ?? null,
            );

            return ApiResponse::created(
                'Indicator created successfully',
                new IndicatorResource($indicator)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @OA\Put(
     *     path="/indicators/{id}",
     *     tags={"Indicators"},
     *     summary="Update existing indicator",
     *     description="Updates information of a performance indicator, including name, target, type and associated measure",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Indicator ID to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "target", "type_id", "measure_id"},
     *            @OA\Property(property="name", type="string", example="Number of certified teachers annually", description="Updated indicator name"),
     *            @OA\Property(property="target", type="number", format="float", example=120.0, description="Updated numeric target"),
     *            @OA\Property(property="type_id", type="integer", example=2, description="Updated indicator type ID"),
     *            @OA\Property(property="measure_id", type="integer", example=1, description="Updated associated measure ID")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Indicator updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Indicator updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Indicator")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Domain validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Indicator not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Technical validation error"
     *     )
     * )
     */
    public function update(IndicatorRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $indicator = $this->indicatorService->updateIndicator(
                $id,
                $validated['name'],
                $validated['target'],
                $validated['type_id'],
                $validated['measure_id'],
                $validated['actual_value'] ?? null,
            );

            return ApiResponse::success(
                'Indicator uploaded successfully',
                200,
                new IndicatorResource($indicator)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @OA\Get(
     *     path="/indicators/search",
     *     tags={"Indicators"},
     *     summary="Search indicator by name",
     *     description="Searches for a specific performance indicator by its name (exact search, case-insensitive)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Indicator name to search",
     *         @OA\Schema(type="string", example="Performance achievement percentage")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Indicator found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Indicator found"),
     *             @OA\Property(property="data", ref="#/components/schemas/Indicator")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Indicator not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Indicator not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Technical validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $indicator = $this->indicatorService->findIndicatorByName($request->input('name'));

            return ApiResponse::success(
                'Indicator found',
                200,
                new IndicatorResource($indicator)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Indicator');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function getIndicatorsByMeasureId(Request $request, int $measureId)
    {
        try {
            $search = $request->get("search");
            $perPage = (int) $request->get("per_page", 10);

            $exclude = (array) $request->input('exclude', []);

            $indicators = $this->indicatorService->getIndicatorsByMeasureId($measureId, $perPage, $search, $exclude);

            return ApiResponse::success(
                'Indicators paginated list successfully uploaded',
                200,
                [
                    'indicators' => IndicatorResource::collection($indicators),
                    'total' => $indicators->count(),
                    'per_page' => $indicators->perPage(),
                    'current_page' => $indicators->currentPage(),
                    'last_page' => $indicators->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/indicators/{id}",
     *     tags={"Indicators"},
     *     summary="Delete indicator",
     *     description="Deletes an indicator. Fails if the indicator is currently assigned to one or more projects.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Indicator ID to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Indicator deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Indicator deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Business validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cannot delete an indicator that is assigned to one or more projects.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->indicatorService->deleteIndicator($id);
            return ApiResponse::success('Indicator deleted successfully', 200);
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
