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

class CurrencyController extends Controller
{
    private CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Listar todas las monedas
     */
    public function index(): JsonResponse
    {
        try {
            $currencies = $this->currencyService->getAllCurrencies();
            
            return ApiResponse::success(
                'Lista de monedas obtenida exitosamente',
                200,
                [
                    'currencies' => CurrencyResource::collection($currencies),
                    'total' => $currencies->count()
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Mostrar una moneda específica
     */
    public function show(int $id): JsonResponse
    {
        try {
            $currency = $this->currencyService->getCurrencyById($id);

            return ApiResponse::success(
                'Moneda encontrada',
                200,
                new CurrencyResource($currency)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Moneda');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Crear una nueva moneda
     */
    public function store(CurrencyRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $currency = $this->currencyService->createCurrency($validated['code']);

            return ApiResponse::created(
                'Moneda creada exitosamente',
                new CurrencyResource($currency)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * Actualizar una moneda existente
     */
    public function update(CurrencyRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $currency = $this->currencyService->updateCurrency($id, $validated['code']);

            return ApiResponse::success(
                'Moneda actualizada exitosamente',
                200,
                new CurrencyResource($currency)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * Buscar moneda por código
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'code' => 'required|string|min:1'
            ]);

            $currency = $this->currencyService->findCurrencyByCode($request->input('code'));

            return ApiResponse::success(
                'Moneda encontrada',
                200,
                new CurrencyResource($currency)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Moneda');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }
}
