<?php

namespace App\Modules\Currency\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\CurrencyRequest;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\CurrencyResource;
use App\Modules\Currency\Service\CurrencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="Currency",
 *     type="object",
 *     title="Currency",
 *     description="Tipos de moneda utilizados en programas y proyectos (USD, EUR, BOB, etc.)",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único de la moneda"),
 *     @OA\Property(property="code", type="string", example="USD", description="Código ISO 4217 de la moneda (3 caracteres, único)")
 * )
 */
class CurrencyController extends Controller
{
    private CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * @OA\Get(
     *     path="/currencies",
     *     tags={"Currencies"},
     *     summary="Listar todas las monedas",
     *     description="Obtiene la lista completa de tipos de moneda disponibles en el sistema (USD, EUR, BOB, etc.)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de monedas obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="currencies",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Currency")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=5, description="Total de monedas registradas")
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
            $currencies = $this->currencyService->getAllCurrencies();

            return ApiResponse::success(
                'Currency List Successfully Obtained',
                200,
                [
                    'currencies' => CurrencyResource::collection($currencies),
                    'total' => $currencies->count()
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
     *     path="/currencies/{id}",
     *     tags={"Currencies"},
     *     summary="Obtener una moneda específica",
     *     description="Obtiene la información detallada de un tipo de moneda por su ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la moneda a obtener",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Moneda encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Moneda encontrada"),
     *             @OA\Property(property="data", ref="#/components/schemas/Currency")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Moneda no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Moneda no encontrado")
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
            $currency = $this->currencyService->getCurrencyById($id);

            return ApiResponse::success(
                'Currency found',
                200,
                new CurrencyResource($currency)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Currency');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/currencies",
     *     tags={"Currencies"},
     *     summary="Crear nueva moneda",
     *     description="Registra un nuevo tipo de moneda. El código debe ser único en el sistema (generalmente código ISO 4217 de 3 caracteres).",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"code"},
     *                 @OA\Property(property="code", type="string", maxLength=3, example="EUR", description="Código ISO 4217 de la moneda (requerido, 3 caracteres, único)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Moneda creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Moneda creada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Currency")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="el código debe tener al menos 2 caracteres")
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
     *                     property="code",
     *                     type="array",
     *                     @OA\Items(type="string", example="Ya existe una moneda con el código: EUR")
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
    public function store(CurrencyRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $currency = $this->currencyService->createCurrency($validated['code']);

            return ApiResponse::created(
                'Currency created successfully',
                new CurrencyResource($currency)
            );
        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Already exist')) {
                return ApiResponse::validationError(['code' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @OA\Put(
     *     path="/currencies/{id}",
     *     tags={"Currencies"},
     *     summary="Actualizar moneda existente",
     *     description="Actualiza el código de un tipo de moneda. El código debe ser único en el sistema.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la moneda a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"code"},
     *                 @OA\Property(property="code", type="string", maxLength=3, example="GBP", description="Código ISO 4217 actualizado (debe ser único)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Moneda actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Moneda actualizada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Currency")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Moneda no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Moneda no encontrado")
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
     *                     property="code",
     *                     type="array",
     *                     @OA\Items(type="string", example="Ya existe una moneda con el código: GBP")
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
    public function update(CurrencyRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $currency = $this->currencyService->updateCurrency($id, $validated['code']);

            return ApiResponse::success(
                'Currency uploaded successfully',
                200,
                new CurrencyResource($currency)
            );
        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Alredy exist')) {
                return ApiResponse::validationError(['code' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @OA\Get(
     *     path="/currencies/search",
     *     tags={"Currencies"},
     *     summary="Buscar moneda por código",
     *     description="Busca un tipo de moneda específico por su código ISO 4217 (búsqueda exacta, case-insensitive)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         required=true,
     *         description="Código de la moneda a buscar",
     *         @OA\Schema(type="string", example="USD")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Moneda encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Moneda encontrada"),
     *             @OA\Property(property="data", ref="#/components/schemas/Currency")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Moneda no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Moneda no encontrado")
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
     *                     property="code",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo code es obligatorio.")
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
                'code' => 'required|string|min:1'
            ]);

            $currency = $this->currencyService->findCurrencyByCode($request->input('code'));

            return ApiResponse::success(
                'Currency found',
                200,
                new CurrencyResource($currency)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Currency');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
