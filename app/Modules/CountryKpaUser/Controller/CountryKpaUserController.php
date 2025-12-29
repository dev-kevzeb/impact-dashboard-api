<?php

namespace App\Modules\CountryKpaUser\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\CountryKpaUserRequest;
use App\Http\Resources\CountryKpaUserResource;
use App\Http\Responses\ApiResponse;
use App\Modules\CountryKpaUser\Service\CountryKpaUserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="CountryKpaUser",
 *     type="object",
 *     title="CountryKpaUser",
 *     description="User assignment to CountryKpa (Country-KPA relationship)",
 *     @OA\Property(property="id", type="integer", example=1, description="Assignment unique ID"),
 *     @OA\Property(
 *         property="country_kpa",
 *         type="object",
 *         description="CountryKpa information",
 *         @OA\Property(property="id", type="integer", example=5),
 *         @OA\Property(property="country", type="string", example="Bolivia"),
 *         @OA\Property(property="kpa", type="string", example="Quality Education")
 *     ),
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         description="User information",
 *         @OA\Property(property="id", type="integer", example=3),
 *         @OA\Property(property="name", type="string", example="John Doe"),
 *         @OA\Property(property="email", type="string", example="john@example.com"),
 *         @OA\Property(property="role", type="string", example="Manager")
 *     ),
 *     @OA\Property(property="assigned_at", type="string", format="date-time", example="2025-12-19T10:30:00Z", description="Assignment creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-19T15:45:00Z", description="Last update timestamp")
 * )
 */
class CountryKpaUserController extends Controller
{
    private CountryKpaUserService $service;
    
    public function __construct(CountryKpaUserService $service)
    {
        $this->service = $service;
    }
    
    /**
     * @OA\Get(
     *     path="/api/v1/country_kpa_users",
     *     summary="Get all user assignments to CountryKpas",
     *     description="Retrieve a list of all user assignments with relationships loaded",
     *     operationId="getCountryKpaUsersList",
     *     tags={"CountryKpaUsers"},
     *     @OA\Parameter(
     *         name="country_kpa_id",
     *         in="query",
     *         description="Filter by CountryKpa ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by User ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=3)
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
     *                     @OA\Items(ref="#/components/schemas/CountryKpaUser")
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
            // Filter by country_kpa_id if provided
            if ($request->has('country_kpa_id')) {
                $countryKpaId = (int) $request->query('country_kpa_id');
                $assignments = $this->service->getUserRolesByCountryKpa($countryKpaId);
                
                return ApiResponse::success(
                    'UserRoles for CountryKpa retrieved successfully',
                    200,
                    [
                        'assignments' => CountryKpaUserResource::collection($assignments),
                        'total' => $assignments->count()
                    ]
                );
            }
            
            // Filter by user_role_id if provided
            if ($request->has('user_role_id')) {
                $userRoleId = (int) $request->query('user_role_id');
                $assignments = $this->service->getCountryKpasByUserRole($userRoleId);
                
                return ApiResponse::success(
                    'CountryKpas for UserRole retrieved successfully',
                    200,
                    [
                        'assignments' => CountryKpaUserResource::collection($assignments),
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
                    'assignments' => CountryKpaUserResource::collection($assignments),
                    'total' => $assignments->count()
                ]
            );
        } catch (Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
    
    /**
     * @OA\Post(
     *     path="/api/v1/country_kpa_users",
     *     summary="Assign a user to a CountryKpa",
     *     description="Create a new assignment between a User and a CountryKpa with optional role validation",
     *     operationId="createCountryKpaUserAssignment",
     *     tags={"CountryKpaUsers"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"country_kpa_id", "user_role_id", "required_role_name"},
     *             @OA\Property(property="country_kpa_id", type="integer", example=5, description="CountryKpa ID"),
     *             @OA\Property(property="user_role_id", type="integer", example=12, description="UserRole ID"),
     *             @OA\Property(property="required_role_name", type="string", example="project_manager", description="Role name to validate (e.g., 'project_manager', 'country_manager', 'admin'). Only UserRoles with this role can be assigned.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Assignment created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User assigned to CountryKpa successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/CountryKpaUser")
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
     *                         property="country_kpa_id",
     *                         type="array",
     *                         @OA\Items(type="string", example="The specified CountryKpa does not exist.")
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
    public function store(CountryKpaUserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            $assignment = $this->service->createAssignment(
                $validated['country_kpa_id'],
                $validated['user_role_id'],
                $validated['required_role_name']
            );
            
            $assignment->load(['countryKpa.country', 'countryKpa.kpa', 'userRole.user.userState', 'userRole.role']);
            
            return ApiResponse::created(
                'UserRole assigned to CountryKpa successfully',
                new CountryKpaUserResource($assignment)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
    
    /**
     * @OA\Get(
     *     path="/api/v1/country_kpa_users/{id}",
     *     summary="Get assignment by ID",
     *     description="Retrieve a specific assignment by its ID with relationships",
     *     operationId="getCountryKpaUserById",
     *     tags={"CountryKpaUsers"},
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
     *             @OA\Property(property="data", ref="#/components/schemas/CountryKpaUser")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assignment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="CountryKpaUser not found with ID: 999"),
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
                new CountryKpaUserResource($assignment)
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
     *     path="/api/v1/country_kpa_users/{id}",
     *     summary="Update an assignment",
     *     description="Update an existing user assignment to a different CountryKpa or different user with optional role validation",
     *     operationId="updateCountryKpaUserAssignment",
     *     tags={"CountryKpaUsers"},
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
     *             required={"country_kpa_id", "user_role_id", "required_role_name"},
     *             @OA\Property(property="country_kpa_id", type="integer", example=8, description="New CountryKpa ID"),
     *             @OA\Property(property="user_role_id", type="integer", example=15, description="New UserRole ID"),
     *             @OA\Property(property="required_role_name", type="string", example="project_manager", description="Role name to validate. Only UserRoles with this role can be assigned.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Assignment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Assignment updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/CountryKpaUser")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assignment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="CountryKpaUser not found with ID: 999"),
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
    public function update(CountryKpaUserRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            $assignment = $this->service->updateAssignment(
                $id,
                $validated['country_kpa_id'],
                $validated['user_role_id'],
                $validated['required_role_name']
            );
            
            $assignment->load(['countryKpa.country', 'countryKpa.kpa', 'userRole.user.userState', 'userRole.role']);
            
            return ApiResponse::success(
                'Assignment updated successfully',
                200,
                new CountryKpaUserResource($assignment)
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
     *     path="/api/v1/country_kpa_users/{id}",
     *     summary="Remove an assignment (physical delete)",
     *     description="Permanently delete an assignment between a User and a CountryKpa",
     *     operationId="deleteCountryKpaUserAssignment",
     *     tags={"CountryKpaUsers"},
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
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assignment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="CountryKpaUser not found with ID: 999"),
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
