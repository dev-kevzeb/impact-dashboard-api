<?php

namespace App\Modules\Role\Controller;

use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
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
 *     description="Roles de usuario del sistema (admin, editor, viewer, etc.)",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del rol"),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="admin",
 *         description="Nombre del rol de usuario (único, máximo 50 caracteres)"
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
     *     summary="Listar roles de usuario",
     *     description="Obtiene todos los roles de usuario disponibles en el sistema",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de roles obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Roles de usuario obtenidos exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Role")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $roles = $this->service->getAllRoles();

            return ApiResponse::success(
                'Roles de usuario obtenidos exitosamente',
                200,
                RoleResource::collection($roles)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/roles",
     *     tags={"Roles"},
     *     summary="Crear rol de usuario",
     *     description="Crea un nuevo rol de usuario. El nombre debe ser único.",
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
     *                     description="Nombre del rol (requerido, único, máximo 50 caracteres, mínimo 2 caracteres)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rol creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rol de usuario creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de negocio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="El nombre no debe ir vacío")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación técnica",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="Este rol ya existe en el sistema")
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
                'Rol de usuario creado exitosamente',
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
     *     summary="Obtener rol por ID",
     *     description="Obtiene la información de un rol de usuario específico",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del rol",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rol encontrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rol de usuario encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rol no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Rol de usuario no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $role = $this->service->findRoleById($id);

            return ApiResponse::success(
                'Rol de usuario encontrado',
                200,
                new RoleResource($role)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Rol de usuario');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/roles/{id}",
     *     tags={"Roles"},
     *     summary="Actualizar rol de usuario",
     *     description="Actualiza el nombre de un rol existente. El nuevo nombre debe ser único.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del rol a actualizar",
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
     *                     description="Nuevo nombre del rol (requerido, único, máximo 50 caracteres, mínimo 2 caracteres)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rol actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rol de usuario actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de negocio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="El nombre debe tener al menos 2 caracteres")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rol no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Rol de usuario no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación técnica",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="Este rol ya existe en el sistema")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function update(RoleRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $role = $this->service->updateRole($id, $validated['name']);

            return ApiResponse::success(
                'Rol de usuario actualizado exitosamente',
                200,
                new RoleResource($role)
            );
        } catch (RuntimeException $e) {
            // Si el mensaje indica que no se encontró, retornar 404
            if (str_contains($e->getMessage(), 'no encontrado')) {
                return ApiResponse::notFound('Rol de usuario');
            }
            // Otros errores de negocio retornan 400
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/roles/search",
     *     tags={"Roles"},
     *     summary="Buscar roles por nombre",
     *     description="Busca roles de usuario por término de búsqueda (búsqueda case-insensitive)",
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         required=true,
     *         description="Término de búsqueda para filtrar roles",
     *         @OA\Schema(type="string", example="admin")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Búsqueda completada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Búsqueda completada"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Role")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Parámetro q inválido",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="q",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo q es obligatorio")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la búsqueda",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al buscar roles")
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
                'Búsqueda completada',
                200,
                RoleResource::collection($roles)
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}
