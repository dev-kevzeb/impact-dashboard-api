<?php

namespace App\Modules\Beneficiary\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\BeneficiaryRequest;
use App\Modules\Beneficiary\Domain\Beneficiary;
use App\Modules\Beneficiary\Service\BeneficiaryService;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\BeneficiaryResource;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="Beneficiary",
 *     type="object",
 *     title="Beneficiary",
 *     description="Beneficiarios o población objetivo de programas y proyectos",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del beneficiario"),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Comunidades Rurales de Potosí",
 *         description="Nombre del beneficiario o grupo objetivo (único, máximo 255 caracteres)"
 *     )
 * )
 */
class BeneficiaryController extends Controller
{
    private BeneficiaryService $beneficiaryService;

    public function __construct(BeneficiaryService $beneficiaryService)
    {
        $this->beneficiaryService = $beneficiaryService;
    }

    /**
     * @OA\Get(
     *     path="/beneficiaries",
     *     tags={"Beneficiaries"},
     *     summary="Listar beneficiarios",
     *     description="Obtiene todos los beneficiarios o grupos objetivo de programas y proyectos",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de beneficiarios obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de beneficiarios obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="beneficiaries",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Beneficiary")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=12)
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

            $beneficiaries = $this->beneficiaryService->getBeneficiariesPaginated($search, $perPage);

            return ApiResponse::success(
                'Beneficiary list successfully obtained',
                200,
                [
                    'beneficiaries' => BeneficiaryResource::collection($beneficiaries),
                    'total' => $beneficiaries->count(),
                    'per_page' => $beneficiaries->perPage(),
                    'current_page' => $beneficiaries->currentPage(),
                    'last_page' => $beneficiaries->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/beneficiaries",
     *     tags={"Beneficiaries"},
     *     summary="Crear beneficiario",
     *     description="Crea un nuevo beneficiario o grupo objetivo. El nombre debe ser único.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="Mujeres emprendedoras de La Paz",
     *                     description="Nombre del beneficiario (requerido, único, máximo 255 caracteres, mínimo 2 caracteres)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Beneficiario creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Beneficiario creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Beneficiary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de negocio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="El nombre del beneficiario no debe ir vacío")
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
     *                     @OA\Items(type="string", example="Este beneficiario ya existe en el sistema")
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
    public function store(BeneficiaryRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $beneficiary = $this->beneficiaryService->createBeneficiary($validated['name']);

            return ApiResponse::created(
                'Beneficiary created successfully',
                new BeneficiaryResource($beneficiary)
            );
        } catch (QueryException $e) {
            // Error 23505 = Unique violation en PostgreSQL
            if ($e->getCode() == 23505 || $e->getCode() === '23505') {
                return ApiResponse::validationError(['name' => ['Este beneficiario ya existe en el sistema.']]);
            }
            return ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/beneficiaries/{id}",
     *     tags={"Beneficiaries"},
     *     summary="Obtener beneficiario por ID",
     *     description="Obtiene la información de un beneficiario específico",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del beneficiario",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Beneficiario encontrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Beneficiario encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Beneficiary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Beneficiario no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Beneficiario no encontrado")
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
            $beneficiary = $this->beneficiaryService->getBeneficiaryById($id);

            return ApiResponse::success(
                'Beneficiary found',
                200,
                new BeneficiaryResource($beneficiary)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Beneficiary');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/beneficiaries/{id}",
     *     tags={"Beneficiaries"},
     *     summary="Actualizar beneficiario",
     *     description="Actualiza el nombre de un beneficiario existente. El nuevo nombre debe ser único.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del beneficiario a actualizar",
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
     *                     example="Jóvenes en situación de riesgo",
     *                     description="Nuevo nombre del beneficiario (requerido, único, máximo 255 caracteres, mínimo 2 caracteres)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Beneficiario actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Beneficiario actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Beneficiary")
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
     *         description="Beneficiario no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Beneficiario no encontrado")
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
     *                     @OA\Items(type="string", example="Este beneficiario ya existe en el sistema")
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
    public function update(BeneficiaryRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $beneficiary = $this->beneficiaryService->updateBeneficiary($id, $validated['name']);

            return ApiResponse::success(
                'Beneficiary uploaded successfully',
                200,
                new BeneficiaryResource($beneficiary)
            );
        } catch (QueryException $e) {
            // Error 23505 = Unique violation en PostgreSQL
            if ($e->getCode() == 23505 || $e->getCode() === '23505') {
                return ApiResponse::validationError(['name' => ['Este beneficiario ya existe en el sistema.']]);
            }
            return ApiResponse::error('Error de base de datos', 500);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Not found')) {
                return ApiResponse::notFound('Beneficiary');
            }
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            // Otros errores de negocio retornan 400
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/beneficiaries/search",
     *     tags={"Beneficiaries"},
     *     summary="Buscar beneficiario por nombre",
     *     description="Busca un beneficiario específico por su nombre (búsqueda exacta, case-insensitive)",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Nombre del beneficiario a buscar",
     *         @OA\Schema(type="string", example="Comunidades Rurales de Potosí")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Beneficiario encontrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Beneficiario encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Beneficiary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Beneficiario no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Beneficiario no encontrado")
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

            $beneficiary = $this->beneficiaryService->findBeneficiaryByName($request->input('name'));

            return ApiResponse::success(
                'Beneficiary found',
                200,
                new BeneficiaryResource($beneficiary)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Beneficiary');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
