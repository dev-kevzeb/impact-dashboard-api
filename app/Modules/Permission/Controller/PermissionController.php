<?php

namespace App\Modules\Permission\Controller;

use App\Http\Resources\PermissionResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

/**
 * @OA\Schema(
 *     schema="Permission",
 *     type="object",
 *     title="Permission",
 *     description="System permissions for role-based access control",
 *     @OA\Property(property="id", type="integer", example=1, description="Unique permission ID"),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="donors:read",
 *         description="Permission name in format {module}:{action}"
 *     ),
 *     @OA\Property(
 *         property="scope",
 *         type="string",
 *         example="donors",
 *         description="Permission scope/module"
 *     ),
 *     @OA\Property(
 *         property="module",
 *         type="string",
 *         example="Donor",
 *         description="Module name"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         example="View donors",
 *         description="Human-readable description"
 *     )
 * )
 */
class PermissionController
{
    /**
     * @OA\Get(
     *     path="/permissions",
     *     tags={"Permissions"},
     *     summary="List all available permissions",
     *     description="Retrieves all permissions available in the system. Use this endpoint to discover permission IDs before assigning them to roles.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Permissions list retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permissions retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Permission")
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
            // Get all permissions from Spatie
            $permissions = Permission::all();

            return ApiResponse::success(
                'Permissions retrieved successfully',
                200,
                PermissionResource::collection($permissions)
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
