<?php

namespace App\Modules\ProgramCountryUserRole\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramCountryUserRoleRequest;
use App\Http\Resources\ProgramCountryUserRoleResource;
use App\Http\Responses\ApiResponse;
use App\Modules\ProgramCountryUserRole\Service\ProgramCountryUserRoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ProgramCountryUserRoleController extends Controller
{
    private ProgramCountryUserRoleService $service;

    public function __construct(ProgramCountryUserRoleService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/program_country_user_roles",
     *     summary="List program-country-user-role assignments",
     *     description="Get paginated list of assignments. Filter by program_id or country_user_role_id.",
     *     operationId="listProgramCountryUserRoles",
     *     tags={"Program Country User Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=10)),
     *     @OA\Parameter(name="program_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="country_user_role_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Assignments retrieved successfully")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage            = (int) $request->get('per_page', 10);
        $programId          = $request->get('program_id') ? (int) $request->get('program_id') : null;
        $countryUserRoleId  = $request->get('country_user_role_id') ? (int) $request->get('country_user_role_id') : null;

        $assignments = $this->service->getAllAssignments($perPage, $programId, $countryUserRoleId);

        return ApiResponse::success(
            'Assignments retrieved successfully',
            200,
            [
                'assignments'  => ProgramCountryUserRoleResource::collection($assignments),
                'total'        => $assignments->total(),
                'per_page'     => $assignments->perPage(),
                'current_page' => $assignments->currentPage(),
                'last_page'    => $assignments->lastPage(),
            ]
        );
    }

    /**
     * @OA\Post(
     *     path="/program_country_user_roles",
     *     summary="Create a program-country-user-role assignment",
     *     description="Link a Program to a CountryUserRole. The program and the country user role must already exist.",
     *     operationId="createProgramCountryUserRole",
     *     tags={"Program Country User Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"program_id","country_user_role_id"},
     *             @OA\Property(property="program_id", type="integer", example=1),
     *             @OA\Property(property="country_user_role_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Assignment created successfully"),
     *     @OA\Response(response=400, description="Assignment already exists"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(ProgramCountryUserRoleRequest $request): JsonResponse
    {
        try {
            $validated  = $request->validated();
            $assignment = $this->service->createAssignment(
                $validated['program_id'],
                $validated['country_user_role_id']
            );

            return ApiResponse::created(
                'Assignment created successfully',
                new ProgramCountryUserRoleResource($assignment)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/program_country_user_roles/{id}",
     *     summary="Get a specific assignment",
     *     operationId="getProgramCountryUserRole",
     *     tags={"Program Country User Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Assignment retrieved successfully"),
     *     @OA\Response(response=404, description="Assignment not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $assignment = $this->service->getAssignmentById($id);

            return ApiResponse::success(
                'Assignment retrieved successfully',
                200,
                new ProgramCountryUserRoleResource($assignment)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/program_country_user_roles/{id}",
     *     summary="Remove an assignment",
     *     description="Unlinks a Program from a CountryUserRole. The program itself is NOT deleted.",
     *     operationId="deleteProgramCountryUserRole",
     *     tags={"Program Country User Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Assignment deleted successfully"),
     *     @OA\Response(response=404, description="Assignment not found")
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
