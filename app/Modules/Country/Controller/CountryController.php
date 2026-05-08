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

/**
 * @OA\Schema(
 *     schema="Country",
 *     type="object",
 *     title="Country",
 *     description="Países donde se ejecutan programas y proyectos, con su moneda oficial",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del país"),
 *     @OA\Property(property="name", type="string", example="Bolivia", description="Nombre del país (único)"),
 *     @OA\Property(
 *         property="currency",
 *         type="object",
 *         description="Moneda oficial del país",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="code", type="string", example="BOB")
 *     )
 * )
 */
class CountryController extends Controller
{
    private CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * @OA\Get(
     *     path="/countries",
     *     tags={"Countries"},
     *     summary="Listar todos los países",
     *     description="Obtiene la lista completa de países donde se ejecutan programas y proyectos, con su moneda oficial",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de países obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="countries",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Country")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=8, description="Total de países registrados")
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
            $active  = $request->has('active') ? filter_var($request->get('active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;

            $countries = $this->countryService->getAllCountries($search, $perPage, $active);

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
            return ApiResponse::error('Internal server error', 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/countries/{id}",
     *     tags={"Countries"},
     *     summary="Obtener un país específico",
     *     description="Obtiene la información detallada de un país por su ID, incluyendo su moneda oficial",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del país a obtener",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="País encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="País encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Country")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="País no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="País no encontrado")
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
            $country = $this->countryService->getCountryById($id);

            return ApiResponse::success(
                'Country found',
                200,
                new CountryResource($country)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Country');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/countries",
     *     tags={"Countries"},
     *     summary="Crear nuevo país",
     *     description="Registra un nuevo país con su moneda oficial. El nombre debe ser único en el sistema.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "currency_id"},
     *                 @OA\Property(property="name", type="string", maxLength=255, example="Perú", description="Nombre del país (requerido, único)"),
     *                 @OA\Property(property="currency_id", type="integer", example=1, description="ID de la moneda oficial del país (requerido, debe existir en Currency)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="País creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="País creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Country")
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
     *                     @OA\Items(type="string", example="Ya existe un país con el nombre: Perú")
     *                 ),
     *                 @OA\Property(
     *                     property="currency_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo currency_id es obligatorio.")
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
    public function store(CountryRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $country = $this->countryService->createCountry(
                $validated['name'],
                $validated['currency'],
                (bool) $validated['active']
            );

            return ApiResponse::created(
                'Country created successfully',
                new CountryResource($country)
            );
        } catch (RuntimeException $e) {
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
     *     path="/countries/{id}",
     *     tags={"Countries"},
     *     summary="Actualizar país existente",
     *     description="Actualiza la información de un país, incluyendo su moneda oficial. El nombre debe ser único.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del país a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "currency_id"},
     *                 @OA\Property(property="name", type="string", maxLength=255, example="Estado Plurinacional de Bolivia", description="Nombre actualizado del país"),
     *                 @OA\Property(property="currency_id", type="integer", example=2, description="ID de la moneda oficial actualizada")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="País actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="País actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Country")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="País no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="País no encontrado")
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
     *                     @OA\Items(type="string", example="Ya existe un país con el nombre: Ecuador")
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
    public function update(CountryRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $country = $this->countryService->updateCountry(
                $id,
                $validated['name'],
                $validated['currency']
            );

            return ApiResponse::success(
                'Country uploaded successfully',
                200,
                new CountryResource($country)
            );
        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            if (str_contains($e->getMessage(), 'cannot be modified because it is active')) {
                return ApiResponse::error($e->getMessage(), 409);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @OA\Get(
     *     path="/countries/search",
     *     tags={"Countries"},
     *     summary="Buscar país por nombre",
     *     description="Busca un país específico por su nombre (búsqueda exacta, case-insensitive)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Nombre del país a buscar",
     *         @OA\Schema(type="string", example="Bolivia")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="País encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="País encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Country")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="País no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="País no encontrado")
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
     *                     @OA\Items(type="string", example="El campo name es obligatorio.")
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

            $country = $this->countryService->findCountryByName($request->input('name'));

            return ApiResponse::success(
                'Country found',
                200,
                new CountryResource($country)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Country');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->countryService->deleteCountry($id);

            return ApiResponse::success(
                'Country deleted successfully',
                200
            );
        } catch (RuntimeException $e) {
            if (str_contains(strtolower($e->getMessage()), 'not found')) {
                return ApiResponse::notFound('Country');
            }

            if (str_contains(strtolower($e->getMessage()), 'cannot be deleted')) {
                return ApiResponse::error($e->getMessage(), 409);
            }

            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/countries/{id}/activate",
     *     tags={"Countries"},
     *     summary="Activar un país",
     *     description="Cambia el estado del país a activo. Solo funciona si el país está inactivo.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del país a activar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="País activado exitosamente"),
     *     @OA\Response(response=409, description="El país ya está activo"),
     *     @OA\Response(response=404, description="País no encontrado")
     * )
     */
    public function activate(int $id): JsonResponse
    {
        try {
            $country = $this->countryService->activateCountry($id);

            return ApiResponse::success(
                'Country activated successfully',
                200,
                new CountryResource($country)
            );
        } catch (RuntimeException $e) {
            if (str_contains(strtolower($e->getMessage()), 'not found')) {
                return ApiResponse::notFound('Country');
            }

            if (str_contains($e->getMessage(), 'already active')) {
                return ApiResponse::error($e->getMessage(), 409);
            }

            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Patch(
     *     path="/countries/{id}/deactivate",
     *     tags={"Countries"},
     *     summary="Desactivar un país",
     *     description="Cambia el estado del país a inactivo. Solo funciona si el país está activo.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del país a desactivar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="País desactivado exitosamente"),
     *     @OA\Response(response=409, description="El país ya está inactivo"),
     *     @OA\Response(response=404, description="País no encontrado")
     * )
     */
    public function deactivate(int $id): JsonResponse
    {
        try {
            $country = $this->countryService->deactivateCountry($id);

            return ApiResponse::success(
                'Country deactivated successfully',
                200,
                new CountryResource($country)
            );
        } catch (RuntimeException $e) {
            if (str_contains(strtolower($e->getMessage()), 'not found')) {
                return ApiResponse::notFound('Country');
            }

            if (str_contains($e->getMessage(), 'already inactive')) {
                return ApiResponse::error($e->getMessage(), 409);
            }

            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}
