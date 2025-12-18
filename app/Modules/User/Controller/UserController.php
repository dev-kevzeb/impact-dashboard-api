<?php

namespace App\Modules\User\Controller;

use App\Http\Controllers\Controller;
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
 *     @OA\Property(property="name", type="string", example="Juan Pérez"),
 *     @OA\Property(property="email", type="string", example="juan@example.com"),
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
     *     path="/api/v1/users",
     *     summary="Get all users with pagination",
     *     description="Retrieve a paginated list of all users with their roles and states",
     *     operationId="getUsersList",
     *     tags={"Users"},
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
     *             @OA\Property(property="message", type="string", example="Usuarios obtenidos exitosamente"),
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
     *             @OA\Property(property="message", type="string", example="Error interno del servidor"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 10);
            $users = $this->service->getAllUsers($perPage);

            return ApiResponse::success(
                'Usuarios obtenidos exitosamente',
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
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users",
     *     summary="Create a new user",
     *     description="Create a new user with email, password, name, role and state",
     *     operationId="createUser",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "role_id", "user_state_id"},
     *             @OA\Property(property="name", type="string", example="Juan Pérez"),
     *             @OA\Property(property="email", type="string", example="juan@example.com"),
     *             @OA\Property(property="password", type="string", example="secret123"),
     *             @OA\Property(property="role_id", type="integer", example=1),
     *             @OA\Property(property="user_state_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Usuario creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Business logic error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="el nombre debe tener al menos 2 caracteres"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="errors",
     *                     type="object",
     *                     @OA\Property(
     *                         property="email",
     *                         type="array",
     *                         @OA\Items(type="string", example="Este email ya está registrado en el sistema.")
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
     *             @OA\Property(property="message", type="string", example="Error interno del servidor"),
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
                $validated['role_id'],
                $validated['user_state_id']
            );

            $user->load(['role', 'userState']);

            return ApiResponse::created(
                'Usuario creado exitosamente',
                new UserResource($user)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/search",
     *     summary="Search users by name",
     *     description="Search for users by name (case-insensitive)",
     *     operationId="searchUsers",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Name to search for",
     *         required=true,
     *         @OA\Schema(type="string", example="Juan")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Usuario encontrado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se encontró el usuario"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor"),
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
                return ApiResponse::error('El parámetro name es requerido', 400);
            }

            $user = $this->service->findUserByName($name);
            $user->load(['role', 'userState']);

            return ApiResponse::success(
                'Usuario encontrado exitosamente',
                200,
                new UserResource($user)
            );
        } catch (RuntimeException $e) {
            if (str_contains(strtolower($e->getMessage()), 'no encontrado')) {
                return ApiResponse::notFound($e->getMessage());
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     summary="Get user by ID",
     *     description="Retrieve a single user by their ID with role and state",
     *     operationId="getUserById",
     *     tags={"Users"},
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
     *             @OA\Property(property="message", type="string", example="Usuario obtenido exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se encontró el usuario con id: 999"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->service->getUserById($id);

            return ApiResponse::success(
                'Usuario obtenido exitosamente',
                200,
                new UserResource($user)
            );
        } catch (RuntimeException $e) {
            if (str_contains(strtolower($e->getMessage()), 'no encontrado')) {
                return ApiResponse::notFound($e->getMessage());
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{id}",
     *     summary="Update an existing user",
     *     description="Update user information (password is optional)",
     *     operationId="updateUser",
     *     tags={"Users"},
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
     *             required={"name", "email", "role_id", "user_state_id"},
     *             @OA\Property(property="name", type="string", example="Juan Pérez Actualizado"),
     *             @OA\Property(property="email", type="string", example="juan.updated@example.com"),
     *             @OA\Property(property="password", type="string", example="newpassword123", description="Optional"),
     *             @OA\Property(property="role_id", type="integer", example=2),
     *             @OA\Property(property="user_state_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Usuario actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Business logic error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="el email no es válido"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se encontró el usuario con id: 999"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="errors",
     *                     type="object",
     *                     @OA\Property(
     *                         property="email",
     *                         type="array",
     *                         @OA\Items(type="string", example="Este email ya está registrado en el sistema.")
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
     *             @OA\Property(property="message", type="string", example="Error interno del servidor"),
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
                $validated['role_id'],
                $validated['user_state_id'],
                $validated['password'] ?? null
            );

            $user->load(['role', 'userState']);

            return ApiResponse::success(
                'Usuario actualizado exitosamente',
                200,
                new UserResource($user)
            );
        } catch (RuntimeException $e) {
            if (str_contains(strtolower($e->getMessage()), 'no encontrado')) {
                return ApiResponse::notFound($e->getMessage());
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }
}
