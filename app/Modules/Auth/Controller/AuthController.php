<?php

namespace App\Modules\Auth\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Modules\Auth\Service\AuthService;
use App\Modules\Auth\Service\RecaptchaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use RuntimeException;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Authentication endpoints"
 * )
 */
class AuthController extends Controller
{
    private AuthService $authService;
    private RecaptchaService $recaptchaService;

    public function __construct(AuthService $authService, RecaptchaService $recaptchaService)
    {
        $this->authService = $authService;
        $this->recaptchaService = $recaptchaService;
    }

    /**
     * @OA\Post(
    *     path="/auth/login",
     *     tags={"Authentication"},
     *     summary="Login user",
     *     description="Authenticates a user and returns JWT token data.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful"
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials")
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

            return ApiResponse::success('Login successful', 200, $result);
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 401);
        } catch (\Exception $e) {
            return ApiResponse::error($e, 500);
        }
    }

    /**
     * @OA\Post(
    *     path="/auth/register",
     *     tags={"Authentication"},
     *     summary="Register user",
     *     description="Creates a new user account with the requested role.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "role_name", "country_id", "g-recaptcha-response"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="role_name", type="string", example="user"),
     *             @OA\Property(property="country_id", type="integer", example=1),
     *             @OA\Property(property="g-recaptcha-response", type="string", example="recaptcha-token")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User created successfully"),
     *     @OA\Response(response=400, description="Business validation error"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $this->recaptchaService->verify($validated['g-recaptcha-response'], $request->ip());

            $result = $this->authService->register(
                $validated['name'],
                $validated['email'],
                $validated['password'],
                $validated['role_name'],
                $validated['country_id']
            );

            return ApiResponse::created(
                $result['message'],
                ['user' => $result['user']]
            );
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
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
     *     @OA\Response(response=401, description="Invalid or expired token")
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
     *     @OA\Response(response=200, description="Authenticated user retrieved successfully"),
     *     @OA\Response(response=401, description="Not authenticated")
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
     * @OA\Put(
    *     path="/auth/profile",
     *     tags={"Authentication"},
     *     summary="Update authenticated user profile",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Jane Doe")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile updated successfully"),
     *     @OA\Response(response=400, description="Business validation error"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $user = $this->authService->updateOwnProfile($validated['name']);

            return ApiResponse::success(
                'Profile updated successfully',
                200,
                new UserResource($user)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
    *     path="/auth/change-password",
     *     tags={"Authentication"},
     *     summary="Change authenticated user password",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "password", "password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="oldpassword123"),
     *             @OA\Property(property="password", type="string", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password updated successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=400, description="Business validation error")
     * )
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $this->authService->changeOwnPassword(
                $validated['current_password'],
                $validated['password']
            );

            return ApiResponse::success('Password updated successfully', 200);
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'Current password is incorrect.') {
                return ApiResponse::validationError([
                    'current_password' => ['Current password is incorrect.']
                ]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Get(
    *     path="/auth/permissions",
     *     tags={"Authentication"},
     *     summary="Get authenticated user permissions",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Permissions retrieved successfully"),
     *     @OA\Response(response=401, description="Not authenticated")
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
