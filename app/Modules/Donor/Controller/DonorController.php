<?php

namespace App\Modules\Donor\Controller;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\DonorResource;
use App\Http\Requests\DonorRequest;
use App\Modules\Donor\Domain\Donor;
use App\Modules\Donor\Service\DonorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="Donor",
 *     type="object",
 *     title="Donor",
 *     description="Donantes que financian proyectos con sus contribuciones",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del donante"),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Banco Mundial",
 *         description="Nombre del donante (único, máximo 255 caracteres)"
 *     ),
 * )
 */
class DonorController extends Controller
{
    private DonorService $donorService;

    public function __construct(DonorService $donorService)
    {
        $this->donorService = $donorService;
    }

    /**
     * @OA\Get(
     *     path="/donors",
     *     tags={"Donors"},
     *     summary="Listar donantes",
     *     description="Obtiene todos los donantes con sus contribuciones y proyectos asociados. Requiere permiso: donors:read o *:*",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de donantes obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de donantes obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="donors",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Donor")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=8)
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
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->get("search");
            $perPage = (int) $request->get("per_page", 10);

            $query = Donor::query();

            if ($search) $query->where("name", "like", "%" . $search . "%");
            $donors = $query->paginate($perPage);

            return ApiResponse::success(
                'Donors list successfully obtained',
                200,
                [
                    'donors' => DonorResource::collection($donors),
                    'total' => $donors->count(),
                    'per_page' => $donors->perPage(),
                    'current_page' => $donors->currentPage(),
                    'last_page' => $donors->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/donors/{id}",
     *     tags={"Donors"},
     *     summary="Obtener donante por ID",
     *     description="Obtiene la información completa de un donante específico. Requiere permiso: donors:read o *:*",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del donante",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Donante encontrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Donante encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Donor")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Donante no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Donante no encontrado")
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
            $donor = $this->donorService->getDonorById($id);

            return ApiResponse::success(
                'Donor found',
                200,
                new DonorResource($donor)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Donor');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/donors",
     *     tags={"Donors"},
     *     summary="Crear donante",
     *     description="Crea un nuevo donante con su contribución y proyecto asociado. El nombre debe ser único. Requiere permiso: donors:write o *:*",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "contribution", "project_id"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="Cooperación Alemana (GIZ)",
     *                     description="Nombre del donante (requerido, único, máximo 255 caracteres)"
     *                 ),
    
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Donante creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Donante creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Donor")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de negocio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="El nombre del donante no debe ir vacío")
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
     *                     @OA\Items(type="string", example="Este donante ya existe en el sistema")
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function store(DonorRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $donor = $this->donorService->createDonor($validated['name']);

            return ApiResponse::created(
                'Donor created successfully',
                new DonorResource($donor)
            );
        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Already exists')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Put(
     *     path="/donors/{id}",
     *     tags={"Donors"},
     *     summary="Actualizar donante",
     *     description="Actualiza la información de un donante existente. El nombre debe ser único. Requiere permiso: donors:write o *:*",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del donante a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "contribution", "project_id"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="Unión Europea",
     *                     description="Nuevo nombre del donante (requerido, único, máximo 255 caracteres)"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Donante actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Donante actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Donor")
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
     *         description="Donante no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Donante no encontrado")
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
     *                     @OA\Items(type="string", example="Este donante ya existe en el sistema")
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
    public function update(DonorRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $donor = $this->donorService->updateDonor($id, $validated['name']);

            return ApiResponse::success(
                'Donor uploaded successfully',
                200,
                new DonorResource($donor)
            );
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Not Found')) {
                return ApiResponse::notFound('Donor');
            }
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/donors/search",
     *     tags={"Donors"},
     *     summary="Buscar donante por nombre",
     *     description="Busca un donante específico por su nombre (búsqueda exacta, case-insensitive). Requiere permiso: donors:read o *:*",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Nombre del donante a buscar",
     *         @OA\Schema(type="string", example="Banco Mundial")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Donante encontrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Donante encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Donor")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Donante no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Donante no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Parámetro name inválido",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo name es obligatorio")
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

            $donor = $this->donorService->findDonorByName($request->input('name'));

            return ApiResponse::success(
                'Donor found',
                200,
                new DonorResource($donor)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Donor');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }


    public function getDonorsExcluding(Request $request): JsonResponse
    {
        try {
            $search = $request->get("search");
            $perPage = (int) $request->get("per_page", 10);
            $exclude = (array) $request->input('exclude', []);

            $donors = $this->donorService->getDonorsExcluding($perPage, $search, $exclude);

            return ApiResponse::success(
                'Donors list successfully obtained',
                200,
                [
                    'donors' => DonorResource::collection($donors),
                    'total' => $donors->count(),
                    'per_page' => $donors->perPage(),
                    'current_page' => $donors->currentPage(),
                    'last_page' => $donors->lastPage(),
                    'all' => $donors->total(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->donorService->deleteDonor($id);

            return ApiResponse::success(
                'Donor deleted successfully',
                200
            );
        } catch (RuntimeException $e) {
            if (str_contains(strtolower($e->getMessage()), 'not found')) {
                return ApiResponse::notFound('Donor');
            }

            if (str_contains(strtolower($e->getMessage()), 'cannot be deleted')) {
                return ApiResponse::error($e->getMessage(), 409);
            }

            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
