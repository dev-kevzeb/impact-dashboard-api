<?php

namespace App\Modules\Auth\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Modules\Auth\Service\AuthService;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Authentication"},
     *     summary="Login user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful"),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->authService->login(
                $validated['email'],
                $validated['password']
            );

            return ApiResponse::success(
                'Login successful',
                200,
                $result
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 401);
        } catch (\Exception $e) {
            return ApiResponse::error('Authentication error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"Authentication"},
     *     summary="Register new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(response=400, description="Business logic error"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->authService->register(
                $validated['name'],
                $validated['email'],
                $validated['password']
            );

            return ApiResponse::created(
                'User registered successfully',
                $result
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/refresh",
     *     tags={"Authentication"},
     *     summary="Refresh access token",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Token refreshed successfully"),
     *     @OA\Response(response=401, description="Token cannot be refreshed")
     * )
     */
    public function refresh(): JsonResponse
    {
        try {
            $result = $this->authService->refresh();
            return ApiResponse::success('Token refreshed', 200, $result);
        } catch (TokenExpiredException $e) {
            return ApiResponse::error('Token cannot be refreshed', 401);
        } catch (TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        }
    }

    /**
     * @OA\Get(
     *     path="/auth/me",
     *     tags={"Authentication"},
     *     summary="Get authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="User retrieved successfully"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function me(): JsonResponse
    {
        try {
            $user = $this->authService->me();
            return ApiResponse::success(
                'Authenticated user',
                200,
                new UserResource($user)
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Not authenticated', 401);
        }
    }

    /**
     * @OA\Get(
     *     path="/auth/permissions",
     *     tags={"Authentication"},
     *     summary="Get current user permissions and roles",
     *     description="Returns all permissions assigned to the authenticated user (via roles and direct permissions)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Permissions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permissions retrieved"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", type="string", example="Admin User"),
     *                 @OA\Property(
     *                     property="permissions",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"*:*"}
     *                 ),
     *                 @OA\Property(
     *                     property="roles",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"admin"}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function permissions(): JsonResponse
    {
        try {
            $user = auth('api')->user();

            return ApiResponse::success('Permissions retrieved', 200, [
                'user' => $user->name,
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'roles' => $user->roles->pluck('name'),
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Not authenticated', 401);
        }
    }
}
