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

class AuthController extends Controller
{
    private AuthService $authService;
    private RecaptchaService $recaptchaService;

    public function __construct(AuthService $authService, RecaptchaService $recaptchaService)
    {
        $this->authService = $authService;
        $this->recaptchaService = $recaptchaService;
    }

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
