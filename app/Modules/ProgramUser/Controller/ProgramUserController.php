<?php

namespace App\Modules\ProgramUser\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramUserRequest;
use App\Http\Resources\ProgramUserResource;
use App\Http\Responses\ApiResponse;
use App\Modules\ProgramUser\Service\ProgramUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ProgramUserController extends Controller
{
    private ProgramUserService $service;

    public function __construct(ProgramUserService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/program_users",
     *     summary="List all Program-CountryKpaUser assignments",
     *     description="Get paginated list of all Program-CountryKpaUser assignments with optional filters",
     *     operationId="listProgramUsers",
     *     tags={"Program Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="program_id",
     *         in="query",
     *         description="Filter by Program ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="country_kpa_user_id",
     *         in="query",
     *         description="Filter by CountryKpaUser ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Assignments retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $programId = $request->get('program_id') ? (int) $request->get('program_id') : null;
        $countryKpaUserId = $request->get('country_kpa_user_id') ? (int) $request->get('country_kpa_user_id') : null;

        $assignments = $this->service->getAllAssignments($perPage, $programId, $countryKpaUserId);

        return ApiResponse::success(
            'Assignments retrieved successfully',
            200,
            [
                'assignments' => ProgramUserResource::collection($assignments),
                'total' => $assignments->total(),
                'per_page' => $assignments->perPage(),
                'current_page' => $assignments->currentPage(),
                'last_page' => $assignments->lastPage(),
            ]
        );
    }

    /**
     * @OA\Post(
     *     path="/program_users",
     *     summary="Create a new Program-CountryKpaUser assignment",
     *     description="Assign a CountryKpaUser to a Program",
     *     operationId="createProgramUser",
     *     tags={"Program Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"program_id", "country_kpa_user_id"},
     *             @OA\Property(property="program_id", type="integer", example=1),
     *             @OA\Property(property="country_kpa_user_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Assignment created successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - Duplicate assignment"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(ProgramUserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $assignment = $this->service->createAssignment(
                $validated['program_id'],
                $validated['country_kpa_user_id']
            );

            $assignment->load(['program', 'countryKpaUser.countryKpa', 'countryKpaUser.userRole.user', 'countryKpaUser.userRole.role']);

            return ApiResponse::created(
                'Assignment created successfully',
                new ProgramUserResource($assignment)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/program_users/{id}",
     *     summary="Get a specific Program-CountryKpaUser assignment",
     *     description="Retrieve details of a specific assignment by ID",
     *     operationId="getProgramUser",
     *     tags={"Program Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Assignment ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assignment not found"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $assignment = $this->service->getAssignmentById($id);

            return ApiResponse::success(
                'Assignment retrieved successfully',
                200,
                new ProgramUserResource($assignment)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/program_users/{id}",
     *     summary="Update a Program-CountryKpaUser assignment",
     *     description="Update an existing assignment",
     *     operationId="updateProgramUser",
     *     tags={"Program Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Assignment ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"program_id", "country_kpa_user_id"},
     *             @OA\Property(property="program_id", type="integer", example=1),
     *             @OA\Property(property="country_kpa_user_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Assignment updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assignment not found"
     *     )
     * )
     */
    public function update(int $id, ProgramUserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $assignment = $this->service->updateAssignment(
                $id,
                $validated['program_id'],
                $validated['country_kpa_user_id']
            );

            $assignment->load(['program', 'countryKpaUser.countryKpa', 'countryKpaUser.userRole.user', 'countryKpaUser.userRole.role']);

            return ApiResponse::success(
                'Assignment updated successfully',
                200,
                new ProgramUserResource($assignment)
            );
        } catch (RuntimeException $e) {
            if (str_contains(strtolower($e->getMessage()), 'not found')) {
                return ApiResponse::notFound($e->getMessage());
            }
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/program_users/{id}",
     *     summary="Delete a Program-CountryKpaUser assignment",
     *     description="Remove an assignment between Program and CountryKpaUser",
     *     operationId="deleteProgramUser",
     *     tags={"Program Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Assignment ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Assignment deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assignment not found"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->removeAssignment($id);

            return ApiResponse::success('Assignment deleted successfully', 200);
        } catch (RuntimeException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }
}
