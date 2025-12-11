<?php

namespace App\Modules\Country\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\CountryRequest;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\CountryResource;
use App\Modules\Country\Service\CountryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CountryController extends Controller
{
    private CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * Listar todos los países
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get("per_page", 10);

            $countries = $this->countryService->getAllCountries($perPage);
            
            return ApiResponse::success(
                'Countries paginated list successfully uploaded',
                200,
                [
                    'countries' => CountryResource::collection($countries),
                    'total' => $countries->count(),
                    'per_page' => $countries->perPage(),
                    'current_page' => $countries->currentPage(),
                    'last_page' => $countries->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Mostrar un país específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $country = $this->countryService->getCountryById($id);

            return ApiResponse::success(
                'País encontrado',
                200,
                new CountryResource($country)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('País');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Crear un nuevo país
     */
    public function store(CountryRequest $request): JsonResponse
    {
        try {
            $validated= $request->validated();

            $country = $this->countryService->createCountry($validated['name'], $validated['currency']);

            return ApiResponse::created(
                'Country successfully created',
                new CountryResource($country)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * Actualizar un país existente
     */
    public function update(CountryRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $country = $this->countryService->updateCountry(
                $id,
                $validated['name'],
                $validated['currency'],
            );

            return ApiResponse::success(
                'Country successfully uploaded',
                200,
                new CountryResource($country)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * Buscar país por nombre
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $country = $this->countryService->findCountryByName($request->input('name'));

            return ApiResponse::success(
                'País encontrado',
                200,
                new CountryResource($country)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::notFound('País');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }
}
