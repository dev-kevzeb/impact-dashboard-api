<?php

namespace App\Modules\UserState\Controller;

use App\Http\Requests\UserStateRequest;
use App\Http\Resources\UserStateResource;
use App\Http\Responses\ApiResponse;
use App\Modules\UserState\Service\UserStateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="UserState",
 *     type="object",
 *     title="UserState",
 *     description="Estados de usuario del sistema (active, inactive, suspended, etc.)",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del estado"),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="active",
 *         description="Nombre del estado de usuario (único, máximo 50 caracteres)"
 *     )
 * )
 */
class UserStateController
{
    private UserStateService $service;

    public function __construct(UserStateService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/user_states",
     *     tags={"User States"},
     *     summary="Listar estados de usuario",
     *     description="Obtiene todos los estados de usuario disponibles en el sistema",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de estados obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estados de usuario obtenidos exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/UserState")
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
            $userStates = $this->service->getAllUserStates();

            return ApiResponse::success(
                'Estados de usuario obtenidos exitosamente',
                200,
                UserStateResource::collection($userStates)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/user_states",
     *     tags={"User States"},
     *     summary="Crear estado de usuario",
     *     description="Crea un nuevo estado de usuario. El nombre debe ser único.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="pending",
     *                     description="Nombre del estado (requerido, único, máximo 50 caracteres, mínimo 2 caracteres)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Estado creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estado de usuario creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserState")
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
     *                     @OA\Items(type="string", example="Este estado de usuario ya existe en el sistema")
     *                 )
     *             )
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/user_states/{id}",
     *     tags={"User States"},
     *     summary="Obtener estado por ID",
     *     description="Obtiene la información de un estado de usuario específico",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del estado",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estado encontrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estado de usuario encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserState")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estado no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Estado de usuario no encontrado")
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
            $userState = $this->service->findUserStateById($id);

            return ApiResponse::success(
                'Estado de usuario encontrado',
                200,
                new UserStateResource($userState)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Estado de usuario');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/user_states/{id}",
     *     tags={"User States"},
     *     summary="Actualizar estado de usuario",
     *     description="Actualiza el nombre de un estado existente. El nuevo nombre debe ser único.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del estado a actualizar",
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
     *                     example="verified",
     *                     description="Nuevo nombre del estado (requerido, único, máximo 50 caracteres, mínimo 2 caracteres)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estado actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estado de usuario actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserState")
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
     *         description="Estado no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Estado de usuario no encontrado")
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
     *                     @OA\Items(type="string", example="Este estado de usuario ya existe en el sistema")
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
            // Si el mensaje indica que no se encontró, retornar 404
            if (str_contains($e->getMessage(), 'no encontrado')) {
                return ApiResponse::notFound('Estado de usuario');
            }
            // Otros errores de negocio retornan 400
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/user_states/search",
     *     tags={"User States"},
     *     summary="Buscar estados por nombre",
     *     description="Busca estados de usuario por término de búsqueda (búsqueda case-insensitive)",
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         required=true,
     *         description="Término de búsqueda para filtrar estados",
     *         @OA\Schema(type="string", example="active")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Búsqueda completada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Búsqueda realizada exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/UserState")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontraron resultados",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se encontraron estados de usuario con ese criterio")
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
     *                     @OA\Items(type="string", example="El parámetro de búsqueda (q) es obligatorio")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la búsqueda",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al buscar estados")
     *         )
     *     )
     * )
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
