<?php

namespace App\Modules\UserState\Controller;

use App\Http\Requests\UserStateRequest;
use App\Http\Resources\UserStateResource;
use App\Http\Responses\ApiResponse;
use App\Modules\UserState\Service\UserStateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class UserStateController
{
    private UserStateService $service;

    public function __construct(UserStateService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/v1/user_states - List all user states
     */
    public function index(): JsonResponse
    {
        try {
            $userStates = $this->service->getAllUserStates();

            return ApiResponse::success(
                'Estados de usuario obtenidos exitosamente',
                200,
                UserStateResource::collection($userStates)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * POST /api/v1/user_states - Create new user state
     */
    public function store(UserStateRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $userState = $this->service->createUserState($validated['name']);

            return ApiResponse::created(
                'Estado de usuario creado exitosamente',
                new UserStateResource($userState)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /api/v1/user_states/{id} - Get specific user state
     */
    public function show(int $id): JsonResponse
    {
        try {
            $userState = $this->service->findUserStateById($id);

            return ApiResponse::success(
                'Estado de usuario encontrado',
                200,
                new UserStateResource($userState)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Estado de usuario no encontrado');
        }
    }

    /**
     * PUT /api/v1/user_states/{id} - Update user state
     */
    public function update(int $id, UserStateRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $userState = $this->service->updateUserState($id, $validated['name']);

            return ApiResponse::success(
                'Estado de usuario actualizado exitosamente',
                200,
                new UserStateResource($userState)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /api/v1/user_states/search?q=term - Search user states
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'q' => 'required|string|min:1'
            ], [
                'q.required' => 'El parámetro de búsqueda (q) es obligatorio.',
                'q.string' => 'El parámetro de búsqueda debe ser una cadena de texto.',
                'q.min' => 'El parámetro de búsqueda debe tener al menos 1 carácter.'
            ]);

            $searchTerm = $request->input('q');
            $userStates = $this->service->searchUserStates($searchTerm);

            if ($userStates->isEmpty()) {
                return ApiResponse::notFound('No se encontraron estados de usuario con ese criterio');
            }

            return ApiResponse::success(
                'Búsqueda realizada exitosamente',
                200,
                UserStateResource::collection($userStates)
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}
