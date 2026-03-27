<?php

namespace App\Modules\Auth\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Modules\Auth\Service\AltchaCaptchaService;
use App\Modules\Auth\Service\AuthService;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    private AuthService $authService;
    private AltchaCaptchaService $altchaCaptchaService;

    public function __construct(AuthService $authService, AltchaCaptchaService $altchaCaptchaService)
    {
        $this->authService = $authService;
        $this->altchaCaptchaService = $altchaCaptchaService;
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
            return ApiResponse::error($e, 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"Authentication"},
     *     summary="Register new user",
     *     description="Register a new user with pending state. Requires admin approval before login is allowed. Only project-manager and country-manager roles can self-register.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
    *             required={"name", "email", "password", "password_confirmation", "role_name", "country_id", "altcha"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(
     *                 property="role_name",
     *                 type="string",
     *                 enum={"project-manager", "country-manager"},
     *                 example="project-manager",
     *                 description="Role to assign (only project-manager and country-manager allowed)"
    *             ),
    *             @OA\Property(property="country_id", type="integer", example=1, description="Country identifier."),
    *             @OA\Property(property="altcha", type="string", example="eyJhbGdvcml0aG0iOiJTSEEtMjU2IiwiY2hhbGxlbmdlIjoiLi4uIiwibnVtYmVyIjo0Mjg1Nywic2FsdCI6Ii4uLiIsInNpZ25hdHVyZSI6Ii4uLiJ9", description="Base64 ALTCHA payload solved by the widget.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully, pending approval",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Registration successful. Your account is pending approval by an administrator."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string"),
     *                 @OA\Property(property="user", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
    *     @OA\Response(response=400, description="Business logic error"),
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
    *                     property="altcha",
    *                     type="array",
    *                     @OA\Items(type="string", example="Captcha validation failed. Please try again.")
    *                 )
    *             )
    *         )
    *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $this->altchaCaptchaService->assertValidPayload($validated['altcha']);

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
     * @OA\Get(
     *     path="/auth/captcha/challenge",
     *     tags={"Authentication"},
     *     summary="Get ALTCHA challenge",
        *     @OA\Response(
        *         response=200,
        *         description="Challenge generated",
        *         @OA\JsonContent(
        *             required={"algorithm", "challenge", "salt", "signature", "maxnumber"},
        *             @OA\Property(property="algorithm", type="string", example="SHA-256"),
        *             @OA\Property(property="challenge", type="string", example="f1cc4e53f5f4d4..."),
        *             @OA\Property(property="salt", type="string", example="a2b3c4d5e6..."),
        *             @OA\Property(property="signature", type="string", example="YjA4YWQ1Yj..."),
        *             @OA\Property(property="maxnumber", type="integer", example=100000)
        *         )
        *     ),
     *     @OA\Response(response=500, description="Captcha configuration error")
     * )
     */
    public function captchaChallenge(): JsonResponse
    {
        try {
            return response()->json($this->altchaCaptchaService->createChallenge());
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
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
