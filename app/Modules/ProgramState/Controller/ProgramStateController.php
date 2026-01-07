<?php

namespace App\Modules\ProgramState\Controller;

use App\Http\Controllers\Controller;
use App\Modules\ProgramState\Service\ProgramStateService;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\ProgramStateResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="ProgramState",
 *     type="object",
 *     title="ProgramState",
 *     description="Program lifecycle states: Inactive, Active, Completed",
 *     @OA\Property(property="id", type="integer", example=1, description="Unique state ID"),
 *     @OA\Property(property="name", type="string", example="Active", description="Program state name")
 * )
 */
class ProgramStateController extends Controller
{
    private ProgramStateService $programStateService;

    public function __construct(ProgramStateService $programStateService)
    {
        $this->programStateService = $programStateService;
    }

    /**
     * @OA\Get(
     *     path="/program_states",
     *     tags={"Program States"},
     *     summary="List all program states",
     *     description="Retrieves the complete list of available states for programs (Inactive, Active, Completed, etc.)",
     *     @OA\Response(
     *         response=200,
     *         description="List retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="State list retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="program_states",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/ProgramState")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=4, description="Total available states")
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
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get("per_page", 10);
            $states = $this->programStateService->getAllProgramStates($perPage);

            return ApiResponse::success(
                'Program states paginated list successfully uploaded',
                200,
                [
                    'program_states' => ProgramStateResource::collection($states),
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
     *     path="/program_states/{id}",
     *     tags={"Program States"},
     *     summary="Get specific state",
     *     description="Retrieves detailed information of a program state by its ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="State ID to retrieve",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="State found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="State found"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProgramState")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="State not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="State not found")
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
            $state = $this->programStateService->getProgramStateById($id);
            return ApiResponse::success(
                'State found',
                200,
                new ProgramStateResource($state)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('State');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/program_states",
     *     tags={"Program States"},
     *     summary="Create new program state",
     *     description="Creates a new state for program lifecycle. The system validates that no state with the same name exists.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string", maxLength=255, example="Under Evaluation", description="New state name (required, unique, min 2 characters)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="State created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="State created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProgramState")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Domain validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="the name must be at least 2 characters")
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
     *                     @OA\Items(type="string", example="A state with the name already exists: Active")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string'
            ]);
            $state = $this->programStateService->createProgramState($request->input('name'));
            return ApiResponse::created(
                'State created successfully',
                new ProgramStateResource($state)
            );
        } catch (RuntimeException $e) {
            // If error is duplicate, return as validation error (422)
            if (str_contains($e->getMessage(), 'already exists')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @OA\Put(
     *     path="/program_states/{id}",
     *     tags={"Program States"},
     *     summary="Update program state",
     *     description="Updates an existing state name. Validates that no other state with the same name exists.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="State ID to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string", maxLength=255, example="Active Modified", description="New state name (required, unique, min 2 characters)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="State updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="State updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProgramState")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Domain validation error or state not found"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="State not found"
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
     *                     @OA\Items(type="string", example="A state with the name already exists: Completed")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string'
            ]);
            $state = $this->programStateService->updateProgramState($id, $request->input('name'));
            return ApiResponse::success(
                'State updated successfully',
                200,
                new ProgramStateResource($state)
            );
        } catch (RuntimeException $e) {
            // If error is duplicate, return as validation error (422)
            if (str_contains($e->getMessage(), 'already exists')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @OA\Get(
     *     path="/program_states/search",
     *     tags={"Program States"},
     *     summary="Search state by name",
     *     description="Searches for a specific state by its name (exact search, case-insensitive)",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="State name to search",
     *         @OA\Schema(type="string", example="Active")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="State found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="State found"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProgramState")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="State not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="State not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
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
            $state = $this->programStateService->findProgramStateByName($request->input('name'));
            return ApiResponse::success(
                'State found',
                200,
                new ProgramStateResource($state)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('State');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
