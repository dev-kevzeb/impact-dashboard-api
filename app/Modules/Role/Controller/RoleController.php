<?php

namespace App\Modules\Role\Controller;

use App\Http\Requests\RoleRequest;
use App\Http\Requests\AssignPermissionRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\PermissionResource;
use App\Http\Responses\ApiResponse;
use App\Modules\Role\Service\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="Role",
 *     type="object",
 *     title="Role",
 *     description="System user roles (admin, editor, viewer, etc.)",
 *     @OA\Property(property="id", type="integer", example=1, description="Unique role ID"),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="admin",
 *         description="User role name (unique, maximum 50 characters)"
 *     )
 * )
 */
class RoleController
{
    private RoleService $service;

    public function __construct(RoleService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/roles",
     *     tags={"Roles"},
     *     summary="List user roles",
     *     description="Retrieves all user roles available in the system",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Role list retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User roles retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Role")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $roles = $this->service->getAllRoles();

            return ApiResponse::success(
                'User roles retrieved successfully',
                200,
                RoleResource::collection($roles)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/roles",
     *     tags={"Roles"},
     *     summary="Create user role",
     *     description="Creates a new user role. The name must be unique.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="editor",
     *                     description="Role name (required, unique, maximum 50 characters, minimum 2 characters)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Role created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User role created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Business validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The name must not be empty")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Technical validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="This role already exists in the system")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(RoleRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $role = $this->service->createRole($validated['name']);

            return ApiResponse::created(
                'User role created successfully',
                new RoleResource($role)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/roles/{id}",
     *     tags={"Roles"},
     *     summary="Get role by ID",
     *     description="Retrieves information of a specific user role",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Role ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role found successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User role found"),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User role not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $role = $this->service->findRoleById($id);

            return ApiResponse::success(
                'User role found',
                200,
                new RoleResource($role)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('User role');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/roles/{id}",
     *     tags={"Roles"},
     *     summary="Update user role",
     *     description="Updates an existing role name. The new name must be unique.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Role ID to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="super_admin",
     *                     description="New role name (required, unique, maximum 50 characters, minimum 2 characters)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User role updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Business validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The name must be at least 2 characters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User role not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Technical validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="This role already exists in the system")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function update(RoleRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $role = $this->service->updateRole($id, $validated['name']);

            return ApiResponse::success(
                'User role updated successfully',
                200,
                new RoleResource($role)
            );
        } catch (RuntimeException $e) {
            // If message indicates not found, return 404
            if (str_contains($e->getMessage(), 'not found')) {
                return ApiResponse::notFound('User role');
            }
            // Other business errors return 400
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/roles/search",
     *     tags={"Roles"},
     *     summary="Search roles by name",
     *     description="Searches user roles by search term (case-insensitive search)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         required=true,
     *         description="Search term to filter roles",
     *         @OA\Schema(type="string", example="admin")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Search completed"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Role")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid q parameter",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="q",
     *                     type="array",
     *                     @OA\Items(type="string", example="The q field is required")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Search error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error searching roles")
     *         )
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'q' => 'required|string|min:1'
            ]);

            $searchTerm = $request->query('q');
            $roles = $this->service->searchRoles($searchTerm);

            return ApiResponse::success(
                'Search completed',
                200,
                RoleResource::collection($roles)
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/roles/{roleId}/permissions",
     *     tags={"Roles"},
     *     summary="List role permissions",
     *     description="Get all permissions assigned to a specific role",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="roleId",
     *         in="path",
     *         required=true,
     *         description="Role ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role permissions retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="donors:read"),
     *                     @OA\Property(property="scope", type="string", example="donors"),
     *                     @OA\Property(property="module", type="string", example="Donor"),
     *                     @OA\Property(property="description", type="string", example="View donors")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Role not found")
     *         )
     *     )
     * )
     */
    public function getPermissions(int $roleId): JsonResponse
    {
        try {
            $permissions = $this->service->getRolePermissions($roleId);

            return ApiResponse::success(
                'Role permissions retrieved successfully',
                200,
                PermissionResource::collection($permissions)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/roles/{roleId}/permissions",
     *     tags={"Roles"},
     *     summary="Assign permission to role",
     *     description="Assign a specific permission to a role. If the permission is already assigned, no action is taken (idempotent).",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="roleId",
     *         in="path",
     *         required=true,
     *         description="Role ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"permission_id"},
     *                 @OA\Property(
     *                     property="permission_id",
     *                     type="integer",
     *                     example=5,
     *                     description="Permission ID to assign"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permission assigned to role successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role or Permission not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Role not found")
     *         )
     *     ),
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
     *                     property="permission_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The specified permission does not exist in the system.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function assignPermission(int $roleId, AssignPermissionRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $role = $this->service->assignPermissionToRole($roleId, $validated['permission_id']);

            return ApiResponse::success(
                'Permission assigned to role successfully',
                200,
                new RoleResource($role)
            );
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'wildcard permission')) {
                return ApiResponse::error($e->getMessage(), 400);
            }

            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * @OA\Delete(
     *     path="/roles/{roleId}/permissions/{permissionId}",
     *     tags={"Roles"},
     *     summary="Remove permission from role",
     *     description="Remove a specific permission from a role",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="roleId",
     *         in="path",
     *         required=true,
     *         description="Role ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="permissionId",
     *         in="path",
     *         required=true,
     *         description="Permission ID to remove",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permission removed from role successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role or Permission not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Role not found")
     *         )
     *     )
     * )
     */
    public function removePermission(int $roleId, int $permissionId): JsonResponse
    {
        try {
            $role = $this->service->removePermissionFromRole($roleId, $permissionId);

            return ApiResponse::success(
                'Permission removed from role successfully',
                200,
                new RoleResource($role)
            );
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'wildcard permission')) {
                return ApiResponse::error($e->getMessage(), 400);
            }

            return ApiResponse::error($e->getMessage(), 404);
        }
    }
}
