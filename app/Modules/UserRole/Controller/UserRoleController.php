<?php

namespace App\Modules\UserRole\Controller;

use App\Http\Requests\UserRoleRequest;
use App\Http\Resources\UserRoleResource;
use App\Http\Responses\ApiResponse;
use App\Modules\UserRole\Service\UserRoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class UserRoleController
{
    private UserRoleService $service;

    public function __construct(UserRoleService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of user roles.
     *
     * GET /api/v1/user_roles
     */
    public function index(): JsonResponse
    {
        try {
            $userRoles = $this->service->getAllUserRoles();

            return ApiResponse::success(
                'Roles de usuario obtenidos exitosamente',
                200,
                UserRoleResource::collection($userRoles)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Store a newly created user role.
     *
     * POST /api/v1/user_roles
     */
    public function store(UserRoleRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $userRole = $this->service->createUserRole($validated['name']);

            return ApiResponse::created(
                'Rol de usuario creado exitosamente',
                new UserRoleResource($userRole)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Display the specified user role.
     *
     * GET /api/v1/user_roles/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $userRole = $this->service->findUserRoleById($id);

            return ApiResponse::success(
                'Rol de usuario encontrado',
                200,
                new UserRoleResource($userRole)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Rol de usuario no encontrado');
        }
    }

    /**
     * Update the specified user role.
     *
     * PUT /api/v1/user_roles/{id}
     */
    public function update(UserRoleRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $userRole = $this->service->updateUserRole($id, $validated['name']);

            return ApiResponse::success(
                'Rol de usuario actualizado exitosamente',
                200,
                new UserRoleResource($userRole)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Search user roles by name.
     *
     * GET /api/v1/user_roles/search?q=admin
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'q' => 'required|string|min:1'
            ]);

            $searchTerm = $request->query('q');
            $userRoles = $this->service->searchUserRoles($searchTerm);

            return ApiResponse::success(
                'Búsqueda completada',
                200,
                UserRoleResource::collection($userRoles)
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}
