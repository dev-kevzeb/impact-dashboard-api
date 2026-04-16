<?php

namespace App\Modules\User\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAdminUserRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Modules\User\Service\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", example="john@example.com"),
 *     @OA\Property(
 *         property="role",
 *         ref="#/components/schemas/Role"
 *     ),
 *     @OA\Property(
 *         property="userState",
 *         ref="#/components/schemas/UserState"
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z")
 * )
 */
class UserController extends Controller
{
    private UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/users",
     *     summary="Get manageable users with pagination",
     *     description="Retrieve a paginated list of manageable users (excludes admin role). Admin manages the system but is not managed by the system.",
     *     operationId="getUsersList",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of users per page (default: 10)",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Users retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="users",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/User")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=50, description="Total number of users"),
     *                 @OA\Property(property="per_page", type="integer", example=10, description="Items per page"),
     *                 @OA\Property(property="current_page", type="integer", example=1, description="Current page number"),
     *                 @OA\Property(property="last_page", type="integer", example=5, description="Last page number")
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
            $perPage = (int) $request->get('per_page', 10);

            // Get manageable users (excludes admin role)
            // Admin manages the system but is not managed by the system
            $users = $this->service->getManageableUsers($perPage);

            return ApiResponse::success(
                'Users retrieved successfully',
                200,
                [
                    'users' => UserResource::collection($users),
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                ]
            );
        } catch (Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/users",
     *     summary="Create a new user",
     *     description="Create a new user with email, password, name and state. Roles must be assigned separately via /api/v1/user_roles",
     *     operationId="createUser",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "user_state_id"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="secret123"),
     *             @OA\Property(property="user_state_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Business logic error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="the name must be at least 2 characters"),
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
     *                         property="email",
     *                         type="array",
     *                         @OA\Items(type="string", example="This email is already registered in the system.")
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
    public function store(UserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $user = $this->service->createUser(
                $validated['name'],
                $validated['email'],
                $validated['password'],
                $validated['user_state_id']
            );

            $user->load(['roles', 'userState']);

            return ApiResponse::created(
                'User created successfully. Use /api/v1/user_roles to assign roles.',
                new UserResource($user)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * Create a new admin user.
     *
     * This endpoint is exclusive for authenticated admins and bypasses
     * public registration requirements (country, recaptcha, email verification flow).
     */
    public function storeAdmin(CreateAdminUserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $user = $this->service->createAdminUser(
                $validated['name'],
                $validated['email'],
                $validated['password']
            );

            return ApiResponse::created(
                'Admin user created successfully. The account is active and ready to login.',
                new UserResource($user)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * Get paginated admin users excluding the authenticated admin.
     */
    public function admins(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 10);
            $users = $this->service->getAdminsExcludingAuthenticated($perPage);

            return ApiResponse::success(
                'Admin users retrieved successfully',
                200,
                [
                    'users' => UserResource::collection($users),
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * Delete an admin account with safety constraints.
     */
    public function destroyAdmin(int $id): JsonResponse
    {
        try {
            $this->service->deleteAdminUser($id);

            return ApiResponse::success(
                'Admin user deleted successfully.',
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

    /**
     * @OA\Get(
     *     path="/users/search",
     *     summary="Search users by name",
     *     description="Search for users by name (case-insensitive)",
     *     operationId="searchUsers",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Name to search for",
     *         required=true,
     *         @OA\Schema(type="string", example="John")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User found successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found"),
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
    public function search(Request $request): JsonResponse
    {
        try {
            $name = $request->query('name');

            if (!$name) {
                return ApiResponse::error('The name parameter is required', 400);
            }

            $user = $this->service->findUserByName($name);
            $user->load(['roles', 'userState']);

            return ApiResponse::success(
                'User found successfully',
                200,
                new UserResource($user)
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
     * @OA\Get(
     *     path="/users/{id}",
     *     summary="Get user by ID",
     *     description="Retrieve a single user by their ID with role and state",
     *     operationId="getUserById",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found with id: 999"),
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
            $user = $this->service->getUserById($id);
            $user->load(['roles', 'userState']);

            return ApiResponse::success(
                'User retrieved successfully',
                200,
                new UserResource($user)
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
     *     path="/users/{id}",
     *     summary="Update an existing user",
     *     description="Update user information (password is optional). Roles must be updated separately via /api/v1/user_roles",
     *     operationId="updateUser",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "user_state_id"},
     *             @OA\Property(property="name", type="string", example="John Doe Updated"),
     *             @OA\Property(property="email", type="string", example="john.updated@example.com"),
     *             @OA\Property(property="password", type="string", example="newpassword123", description="Optional"),
     *             @OA\Property(property="user_state_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Business logic error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="the email is not valid"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found with id: 999"),
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
     *                         property="email",
     *                         type="array",
     *                         @OA\Items(type="string", example="This email is already registered in the system.")
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
    public function update(UserRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $user = $this->service->updateUser(
                $id,
                $validated['name'],
                $validated['email'],
                $validated['user_state_id'],
                $validated['password'] ?? null
            );

            $user->load(['roles', 'userState']);

            return ApiResponse::success(
                'User updated successfully. Use /api/v1/user_roles to manage roles.',
                200,
                new UserResource($user)
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
     * @OA\Get(
     *     path="/users/pending",
     *     summary="Get pending users waiting for approval",
     *     description="Retrieve paginated list of users with pending state. Requires admin permissions (users:write or *:*).",
     *     operationId="getPendingUsers",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of users per page (default: 10)",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pending users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pending users retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="users",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/User")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="current_page", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized - Invalid or missing token"),
     *     @OA\Response(response=403, description="Forbidden - Insufficient permissions")
     * )
     */
    public function pending(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $users = $this->service->getPendingUsers($perPage);

            return ApiResponse::success(
                'Pending users retrieved successfully',
                200,
                [
                    'users' => UserResource::collection($users),
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage()
                ]
            );
        } catch (Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/users/unverified",
     *     summary="Get unverified users (email not verified)",
     *     description="Retrieve paginated list of users with unverified state. These users registered but haven't verified their email yet. Requires admin permissions (users:write or *:*).",
     *     operationId="getUnverifiedUsers",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of users per page (default: 10)",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unverified users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Unverified users retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="users",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/User")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="current_page", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized - Invalid or missing token"),
     *     @OA\Response(response=403, description="Forbidden - Insufficient permissions")
     * )
     */
    public function unverified(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $users = $this->service->getUnverifiedUsers($perPage);

            return ApiResponse::success(
                'Unverified users retrieved successfully',
                200,
                [
                    'users' => UserResource::collection($users),
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage()
                ]
            );
        } catch (Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/users/{id}/approve",
     *     summary="Approve pending user",
     *     description="Change user state from pending to active. User will be able to login after approval. Requires admin permissions (users:write or *:*).",
     *     operationId="approveUser",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID to approve",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User approved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User approved successfully. They can now login."),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=400, description="User is not pending or business logic error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Insufficient permissions"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function approve(int $id): JsonResponse
    {
        try {
            $user = $this->service->approveUser($id);

            return ApiResponse::success(
                'User approved successfully. They can now login.',
                200,
                new UserResource($user)
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
     * @OA\Delete(
     *     path="/users/{id}/reject",
     *     summary="Reject unverified or pending user",
     *     description="Permanently delete a user registration. Accepts users in 'unverified' (email not verified) or 'pending' (waiting approval) states. Use for unwanted/spam registrations or incomplete sign-ups. Requires admin permissions (users:write or *:*).",
     *     operationId="rejectUser",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID to reject",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User rejected and deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User registration rejected and deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=400, description="User is not in unverified/pending state or business logic error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Insufficient permissions"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function reject(int $id): JsonResponse
    {
        try {
            $this->service->rejectUser($id);

            return ApiResponse::success(
                'User registration rejected and deleted successfully.',
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

    /**
     * @OA\Put(
     *     path="/users/{id}/state",
     *     summary="Change user state",
     *     description="Toggle user state between active and inactive. Inactive users cannot login.",
     *     operationId="changeUserState",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"state"},
     *             @OA\Property(property="state", type="string", enum={"active", "inactive"}, example="inactive")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User state changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User state changed to inactive"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Business logic error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User is already in 'inactive' state."),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Resource not found"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function changeUserState(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'state' => 'required|string|in:active,inactive'
            ]);

            $user = $this->service->changeUserState($id, $validated['state']);

            return ApiResponse::success(
                "User state changed to {$validated['state']}",
                200,
                new UserResource($user)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
