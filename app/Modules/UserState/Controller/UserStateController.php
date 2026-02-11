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
     *     summary="List user states",
     *     description="Retrieves all user states available in the system",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="States list retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User states retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/UserState")
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
            $userStates = $this->service->getAllUserStates();

            return ApiResponse::success(
                'User states retrieved successfully',
                200,
                UserStateResource::collection($userStates)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/user_states",
     *     tags={"User States"},
     *     summary="Create user state",
     *     description="Creates a new user state. The name must be unique.",
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
     *                     example="pending",
     *                     description="State name (required, unique, maximum 50 characters, minimum 2 characters)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="State created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User state created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserState")
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
                'User state created successfully',
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
     *     summary="Get state by ID",
     *     description="Retrieves a specific user state information",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="State ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="State found successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User state found"),
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
     *         description="Internal server error"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $userState = $this->service->findUserStateById($id);

            return ApiResponse::success(
                'User state found',
                200,
                new UserStateResource($userState)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Estado de usuario');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/user_states/{id}",
     *     tags={"User States"},
     *     summary="Update user state",
     *     description="Updates an existing state name. The new name must be unique.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="State ID to update",
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
     *                     description="New state name (required, unique, maximum 50 characters, minimum 2 characters)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="State updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User state updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserState")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de negocio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The name must be at least 2 characters")
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
                'User state updated successfully',
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
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/user_states/search",
     *     tags={"User States"},
     *     summary="Search states by name",
     *     description="Searches user states by search term (case-insensitive search)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         required=true,
     *         description="Search term to filter states",
     *         @OA\Schema(type="string", example="active")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Search completed successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/UserState")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No results found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No user states found with that criteria")
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
     *                     @OA\Items(type="string", example="The search parameter (q) is required")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Search error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error searching states")
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
                'q.required' => 'The search parameter (q) is required.',
                'q.string' => 'The search parameter must be a string.',
                'q.min' => 'The search parameter must be at least 1 character.'
            ]);

            $searchTerm = $request->input('q');
            $userStates = $this->service->searchUserStates($searchTerm);

            if ($userStates->isEmpty()) {
                return ApiResponse::notFound('No user states found with that criteria');
            }

            return ApiResponse::success(
                'Search completed successfully',
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
