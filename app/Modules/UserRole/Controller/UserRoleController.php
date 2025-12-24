<?php

namespace App\Modules\UserRole\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRoleRequest;
use App\Http\Resources\UserRoleResource;
use App\Http\Responses\ApiResponse;
use App\Modules\UserRole\Service\UserRoleService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="UserRole",
 *     type="object",
 *     title="UserRole",
 *     description="User-Role assignment (intermediate entity)",
 *     @OA\Property(property="id", type="integer", example=1, description="Assignment unique ID"),
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         description="User information",
 *         @OA\Property(property="id", type="integer", example=3),
 *         @OA\Property(property="name", type="string", example="John Doe"),
 *         @OA\Property(property="email", type="string", example="john@example.com"),
 *         @OA\Property(property="state", type="string", example="Active")
 *     ),
 *     @OA\Property(
 *         property="role",
 *         type="object",
 *         description="Role information",
 *         @OA\Property(property="id", type="integer", example=2),
 *         @OA\Property(property="name", type="string", example="Manager")
 *     ),
 *     @OA\Property(property="assigned_at", type="string", format="date-time", example="2025-12-22T10:30:00Z", description="Assignment creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-22T15:45:00Z", description="Last update timestamp")
 * )
 */
class UserRoleController extends Controller
{
    private UserRoleService $service;

    public function __construct(UserRoleService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user_roles",
     *     summary="Get all user-role assignments",
     *     description="Retrieve a list of all user-role assignments with relationships loaded",
     *     operationId="getUserRolesList",
     *     tags={"UserRoles"},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by User ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Parameter(
     *         name="role_id",
     *         in="query",
     *         description="Filter by Role ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Assignments retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="assignments",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/UserRole")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=15, description="Total number of assignments")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Filter by user_id if provided
            if ($request->has('user_id')) {
                $userId = (int) $request->query('user_id');
                $assignments = $this->service->getRolesByUser($userId);

                return ApiResponse::success(
                    'Roles for User retrieved successfully',
                    200,
                    [
                        'assignments' => UserRoleResource::collection($assignments),
                        'total' => $assignments->count()
                    ]
                );
            }

            // Filter by role_id if provided
            if ($request->has('role_id')) {
                $roleId = (int) $request->query('role_id');
                $assignments = $this->service->getUsersByRole($roleId);

                return ApiResponse::success(
                    'Users with Role retrieved successfully',
                    200,
                    [
                        'assignments' => UserRoleResource::collection($assignments),
                        'total' => $assignments->count()
                    ]
                );
            }

            // Get all assignments
            $assignments = $this->service->getAllAssignments();

            return ApiResponse::success(
                'Assignments retrieved successfully',
                200,
                [
                    'assignments' => UserRoleResource::collection($assignments),
                    'total' => $assignments->count()
                ]
            );
        } catch (Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user_roles",
     *     summary="Assign a role to a user",
     *     description="Create a new assignment between a User and a Role",
     *     operationId="createUserRoleAssignment",
     *     tags={"UserRoles"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "role_id"},
     *             @OA\Property(property="user_id", type="integer", example=3, description="User ID"),
     *             @OA\Property(property="role_id", type="integer", example=2, description="Role ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Assignment created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role assigned to User successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserRole")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="errors",
     *                     type="object",
     *                     @OA\Property(
     *                         property="user_id",
     *                         type="array",
     *                         @OA\Items(type="string", example="The specified User does not exist.")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function store(UserRoleRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $assignment = $this->service->assignRoleToUser(
                $validated['user_id'],
                $validated['role_id']
            );

            $assignment->load(['user.userState', 'role']);

            return ApiResponse::created(
                'Role assigned to User successfully',
                new UserRoleResource($assignment)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user_roles/{id}",
     *     summary="Get assignment by ID",
     *     description="Retrieve a specific assignment by its ID with relationships",
     *     operationId="getUserRoleById",
     *     tags={"UserRoles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Assignment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Assignment retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserRole")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assignment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="UserRole not found with ID: 999"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="data", type="null")
     *         )
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
                new UserRoleResource($assignment)
            );
        } catch (RuntimeException $e) {
            if (str_contains(strtolower($e->getMessage()), 'not found')) {
                return ApiResponse::notFound($e->getMessage());
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/user_roles/{id}",
     *     summary="Update an assignment",
     *     description="Update an existing user-role assignment",
     *     operationId="updateUserRoleAssignment",
     *     tags={"UserRoles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Assignment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "role_id"},
     *             @OA\Property(property="user_id", type="integer", example=5, description="New User ID"),
     *             @OA\Property(property="role_id", type="integer", example=3, description="New Role ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Assignment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Assignment updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserRole")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assignment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="UserRole not found with ID: 999"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="errors",
     *                     type="object",
     *                     @OA\Property(
     *                         property="role_id",
     *                         type="array",
     *                         @OA\Items(type="string", example="The specified Role does not exist.")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function update(UserRoleRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $assignment = $this->service->updateAssignment(
                $id,
                $validated['user_id'],
                $validated['role_id']
            );

            $assignment->load(['user.userState', 'role']);

            return ApiResponse::success(
                'Assignment updated successfully',
                200,
                new UserRoleResource($assignment)
            );
        } catch (RuntimeException $e) {
            if (str_contains(strtolower($e->getMessage()), 'not found')) {
                return ApiResponse::notFound($e->getMessage());
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/user_roles/{id}",
     *     summary="Remove an assignment (physical delete)",
     *     description="Permanently delete an assignment between a User and a Role",
     *     operationId="deleteUserRoleAssignment",
     *     tags={"UserRoles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Assignment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Assignment removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Assignment removed successfully"),
     *             @OA\Property(property="data", type="null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assignment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="UserRole not found with ID: 999"),
     *             @OA\Property(property="data", type="null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="data", type="null)
     *         )
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->removeAssignment($id);

            return ApiResponse::success(
                'Assignment removed successfully',
                200
            );
        } catch (RuntimeException $e) {
            if (str_contains(strtolower($e->getMessage()), 'not found')) {
                return ApiResponse::notFound($e->getMessage());
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
