<?php

namespace App\Modules\ProgramState\Controller;

use App\Http\Controllers\Controller;
use App\Modules\ProgramState\Service\ProgramStateService;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\ProgramStateResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="ProgramState",
 *     type="object",
 *     title="Estado de Programa",
 *     description="Estado del ciclo de vida de un programa (Inactivo, Activo, Finalizado, etc.)",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del estado"),
 *     @OA\Property(property="name", type="string", example="Activo", description="Nombre del estado del programa")
 * )
 */
class ProgramStateController extends Controller
{
    private ProgramStateService $programStateService;

    public function __construct(ProgramStateService $programStateService)
    {
        $this->programStateService = $programStateService;
    }

    /**
     * @OA\Get(
     *     path="/program_states",
     *     tags={"Program States"},
     *     summary="Listar todos los estados de programa",
     *     description="Obtiene la lista completa de estados disponibles para los programas (Inactivo, Activo, Finalizado, etc.)",
     *     @OA\Response(
     *         response=200,
     *         description="Lista obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de estados obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="program_states",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/ProgramState")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=4, description="Total de estados disponibles")
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
            $states = $this->programStateService->getAllProgramStates();
            return ApiResponse::success(
                'Lista de estados obtenida exitosamente',
                200,
                [
                    'program_states' => ProgramStateResource::collection($states),
                    'total' => $states->count()
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/program_states/{id}",
     *     tags={"Program States"},
     *     summary="Obtener un estado específico",
     *     description="Obtiene la información detallada de un estado de programa por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del estado a obtener",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estado encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estado encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProgramState")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estado no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Estado no encontrado")
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
            $state = $this->programStateService->getProgramStateById($id);
            return ApiResponse::success(
                'Estado encontrado',
                200,
                new ProgramStateResource($state)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Estado');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/program_states",
     *     tags={"Program States"},
     *     summary="Crear nuevo estado de programa",
     *     description="Crea un nuevo estado para el ciclo de vida de programas. El sistema valida que no exista un estado con el mismo nombre.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string", maxLength=255, example="En Evaluación", description="Nombre del nuevo estado (requerido, único, mín 2 caracteres)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Estado creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estado creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProgramState")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="el nombre debe tener al menos 2 caracteres")
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
     *                     @OA\Items(type="string", example="Ya existe un estado con el nombre: Activo")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string'
            ]);
            $state = $this->programStateService->createProgramState($request->input('name'));
            return ApiResponse::created(
                'Estado creado exitosamente',
                new ProgramStateResource($state)
            );
        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @OA\Put(
     *     path="/program_states/{id}",
     *     tags={"Program States"},
     *     summary="Actualizar estado de programa",
     *     description="Actualiza el nombre de un estado existente. Valida que no exista otro estado con el mismo nombre.",
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
     *                 @OA\Property(property="name", type="string", maxLength=255, example="Activo Modificado", description="Nuevo nombre del estado (requerido, único, mín 2 caracteres)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estado actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estado actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProgramState")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio o estado no encontrado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estado no encontrado"
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
     *                     @OA\Items(type="string", example="Ya existe un estado con el nombre: Finalizado")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string'
            ]);
            $state = $this->programStateService->updateProgramState($id, $request->input('name'));
            return ApiResponse::success(
                'Estado actualizado exitosamente',
                200,
                new ProgramStateResource($state)
            );
        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @OA\Get(
     *     path="/program_states/search",
     *     tags={"Program States"},
     *     summary="Buscar estado por nombre",
     *     description="Busca un estado específico por su nombre (búsqueda exacta, case-insensitive)",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Nombre del estado a buscar",
     *         @OA\Schema(type="string", example="Activo")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estado encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estado encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProgramState")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estado no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Estado no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo nombre es obligatorio.")
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
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|min:1'
            ]);
            $state = $this->programStateService->findProgramStateByName($request->input('name'));
            return ApiResponse::success(
                'Estado encontrado',
                200,
                new ProgramStateResource($state)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Estado');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }
}
