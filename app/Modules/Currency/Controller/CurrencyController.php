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
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

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
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

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
