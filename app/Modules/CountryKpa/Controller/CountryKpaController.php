<?php

namespace App\Modules\CountryKpa\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\CountryKpaRequest;
use App\Http\Resources\CountryKpaResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use \Illuminate\Http\JsonResponse;
use App\Modules\CountryKpa\Service\CountryKpaService;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="CountryKpa",
 *     type="object",
 *     title="CountryKpa",
 *     description="Relación muchos-a-muchos entre países y áreas prioritarias clave",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único de la relación"),
 *     @OA\Property(property="id_country", type="integer", example=1, description="ID del país (FK a country)"),
 *     @OA\Property(property="id_kpa", type="integer", example=2, description="ID del KPA (FK a kpa)"),
 *     @OA\Property(
 *         property="country",
 *         type="object",
 *         description="Información del país",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Bolivia")
 *     ),
 *     @OA\Property(
 *         property="kpa",
 *         type="object",
 *         description="Información del KPA",
 *         @OA\Property(property="id", type="integer", example=2),
 *         @OA\Property(property="name", type="string", example="Educación de Calidad")
 *     )
 * )
 */
class CountryKpaController extends Controller
{
	protected CountryKpaService $service;

	public function __construct(CountryKpaService $service)
	{
		$this->service = $service;
	}

	/**
	 * @OA\Get(
	 *     path="/country_kpas",
	 *     tags={"Country-KPAs"},
	 *     summary="Listar relaciones Country-KPA",
	 *     description="Obtiene todas las relaciones país-KPA. Opcionalmente filtra por país específico usando parámetro ?country=X",
	 *     security={{"bearerAuth":{}}},
	 *     @OA\Parameter(
	 *         name="country",
	 *         in="query",
	 *         required=false,
	 *         description="ID del país para filtrar sus KPAs asociados",
	 *         @OA\Schema(type="integer", example=1)
	 *     ),
	 *     @OA\Response(
	 *         response=200,
	 *         description="Lista obtenida exitosamente",
	 *         @OA\JsonContent(
	 *             @OA\Property(property="success", type="boolean", example=true),
	 *             @OA\Property(property="message", type="string", example="Lista obtenida"),
	 *             @OA\Property(
	 *                 property="data",
	 *                 type="object",
	 *                 @OA\Property(
	 *                     property="items",
	 *                     type="array",
	 *                     @OA\Items(ref="#/components/schemas/CountryKpa")
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
			if ($request->has('country')) {
				$countryId = (int) $request->query('country');
				
				// Verify user has access to this country
				if (!$this->service->userHasAccessToCountry($countryId)) {
					return ApiResponse::error('Access denied to this country', 403);
				}
				
				$search = $request->get("search");
				$perPage = (int) $request->get("per_page", 10);
				$countryWithKpas = $this->service->getCountryKpasByCountryId($countryId, $search, $perPage);
				return ApiResponse::success('Country KPAs obtained', 200, $countryWithKpas);
			}

			// Only admins can see all countries' KPAs without filter
			$user = auth('api')->user();
			$isAdmin = $user && $user->roles && $user->roles->contains(fn($role) => strtolower($role->name) === 'admin');
			
			if (!$isAdmin) {
				return ApiResponse::error('Only administrators can view all countries', 403);
			}

			$items = $this->service->getAll();
			return ApiResponse::success('Obtained list', 200, ['items' => $items, 'total' => count($items)]);
		} catch (RuntimeException $e) {
			return ApiResponse::error($e->getMessage(), 500);
		}
	}

	/**
	 * @OA\Get(
	 *     path="/country_kpas/{id}",
	 *     tags={"Country-KPAs"},
	 *     summary="Obtener relación Country-KPA específica",
	 *     description="Obtiene el detalle de una relación país-KPA por su ID",
	 *     security={{"bearerAuth":{}}},
	 *     @OA\Parameter(
	 *         name="id",
	 *         in="path",
	 *         required=true,
	 *         description="ID de la relación country_kpa",
	 *         @OA\Schema(type="integer", example=1)
	 *     ),
	 *     @OA\Response(
	 *         response=200,
	 *         description="Registro obtenido",
	 *         @OA\JsonContent(
	 *             @OA\Property(property="success", type="boolean", example=true),
	 *             @OA\Property(property="message", type="string", example="Registro obtenido"),
	 *             @OA\Property(property="data", ref="#/components/schemas/CountryKpa")
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=404,
	 *         description="CountryKpa no encontrado",
	 *         @OA\JsonContent(
	 *             @OA\Property(property="success", type="boolean", example=false),
	 *             @OA\Property(property="message", type="string", example="CountryKpa no encontrado")
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=500,
	 *         description="Error interno del servidor"
	 *     )
	 * )
	 */
	public function show($id): JsonResponse
	{
		try {
			$countryKpa = $this->service->getById((int)$id);
			
			// Verify user has access to this country
			if (isset($countryKpa->id_country) && !$this->service->userHasAccessToCountry($countryKpa->id_country)) {
				return ApiResponse::error('Access denied to this country', 403);
			}
			
			return ApiResponse::success('Registration obtained', 200, $countryKpa);
		} catch (RuntimeException $e) {
			return ApiResponse::notFound('CountryKpa');
		} catch (\Exception $e) {
			return ApiResponse::error('Internal server error', 500);
		}
	}

	/**
	 * @OA\Post(
	 *     path="/country_kpas",
	 *     tags={"Country-KPAs"},
	 *     summary="Crear relación Country-KPA",
	 *     description="Crea una nueva relación entre un país y un KPA",
	 *     security={{"bearerAuth":{}}},
	 *     @OA\RequestBody(
	 *         required=true,
	 *         @OA\JsonContent(
	 *             required={"id_country","id_kpa"},
	 *             @OA\Property(
	 *                 property="id_country",
	 *                 type="integer",
	 *                 example=1,
	 *                 description="ID del país"
	 *             ),
	 *             @OA\Property(
	 *                 property="id_kpa",
	 *                 type="integer",
	 *                 example=2,
	 *                 description="ID del KPA"
	 *             )
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=201,
	 *         description="Relación creada exitosamente",
	 *         @OA\JsonContent(
	 *             @OA\Property(property="success", type="boolean", example=true),
	 *             @OA\Property(
	 *                 property="message",
	 *                 type="string",
	 *                 example="Country-KPA relationship created successfully"
	 *             ),
	 *             @OA\Property(
	 *                 property="data",
	 *                 ref="#/components/schemas/CountryKpa"
	 *             )
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=422,
	 *         description="Error de validación",
	 *         @OA\JsonContent(
	 *             @OA\Property(property="success", type="boolean", example=false),
	 *             @OA\Property(
	 *                 property="message",
	 *                 type="string",
	 *                 example="Validation error"
	 *             ),
	 *             @OA\Property(
	 *                 property="errors",
	 *                 type="object",
	 *                 example={
	 *                     "id_country": {"required"},
	 *                     "id_kpa": {"required"}
	 *                 }
	 *             )
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=400,
	 *         description="Error de dominio",
	 *         @OA\JsonContent(
	 *             @OA\Property(property="success", type="boolean", example=false),
	 *             @OA\Property(property="message", type="string")
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=500,
	 *         description="Error interno del servidor",
	 *         @OA\JsonContent(
	 *             @OA\Property(property="success", type="boolean", example=false),
	 *             @OA\Property(property="message", type="string")
	 *         )
	 *     )
	 * )
	 */
	public function store(CountryKpaRequest $request): JsonResponse
	{
		try {
			// Only admins can create country-KPA relationships
			$user = auth('api')->user();
			$isAdmin = $user && $user->roles && $user->roles->contains(fn($role) => strtolower($role->name) === 'admin');
			
			if (!$isAdmin) {
				return ApiResponse::error('Only administrators can create country-KPA relationships', 403);
			}
			
			$validated = $request->validated();

			$created = $this->service->create($validated);

			return ApiResponse::created('Country-KPA relationship created successfully', $created);
		} catch (\Illuminate\Validation\ValidationException $e) {
			return ApiResponse::validationError($e->errors());
		} catch (RuntimeException $e) {
			return ApiResponse::error($e->getMessage(), 400);
		} catch (\Exception $e) {
			return ApiResponse::error($e->getMessage(), 500);
		}
	}

	/**
	 * @OA\Put(
	 *     path="/country_kpas/{id}",
	 *     tags={"Country-KPAs"},
	 *     summary="Actualizar relación Country-KPA",
	 *     description="Actualiza una relación existente país-KPA (cambia el país o KPA asociado)",
	 *     security={{"bearerAuth":{}}},
	 *     @OA\Parameter(
	 *         name="id",
	 *         in="path",
	 *         required=true,
	 *         description="ID de la relación country_kpa a actualizar",
	 *         @OA\Schema(type="integer", example=1)
	 *     ),
	 *     @OA\RequestBody(
	 *         required=true,
	 *         @OA\MediaType(
	 *             mediaType="application/json",
	 *             @OA\Schema(
	 *                 required={"id_country", "id_kpa"},
	 *                 @OA\Property(property="id_country", type="integer", example=2, description="ID del país actualizado"),
	 *                 @OA\Property(property="id_kpa", type="integer", example=3, description="ID del KPA actualizado")
	 *             )
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=200,
	 *         description="Actualizado exitosamente",
	 *         @OA\JsonContent(
	 *             @OA\Property(property="success", type="boolean", example=true),
	 *             @OA\Property(property="message", type="string", example="Actualizado exitosamente"),
	 *             @OA\Property(property="data", ref="#/components/schemas/CountryKpa")
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=400,
	 *         description="Error de validación de dominio"
	 *     ),
	 *     @OA\Response(
	 *         response=404,
	 *         description="CountryKpa no encontrado"
	 *     ),
	 *     @OA\Response(
	 *         response=422,
	 *         description="Error de validación técnica"
	 *     ),
	 *     @OA\Response(
	 *         response=500,
	 *         description="Error interno del servidor"
	 *     )
	 * )
	 */
	public function update(CountryKpaRequest $request, int $id): JsonResponse
	{
		try {
			// Verify user has access to the country being updated
			$countryKpa = $this->service->getById($id);
			if (isset($countryKpa->id_country) && !$this->service->userHasAccessToCountry($countryKpa->id_country)) {
				return ApiResponse::error('Access denied to this country', 403);
			}
			
			$validated = $request->validated();

			// Also verify access to the new country if it's being changed
			if (isset($validated['id_country']) && $validated['id_country'] !== $countryKpa->id_country) {
				if (!$this->service->userHasAccessToCountry($validated['id_country'])) {
					return ApiResponse::error('Access denied to the target country', 403);
				}
			}

			$updated = $this->service->update($id, $validated);

			return ApiResponse::success(
				'Country-KPA relationship updated successfully',
				200,
				$updated
			);
		} catch (\Illuminate\Validation\ValidationException $e) {
			return ApiResponse::validationError($e->errors());
		} catch (RuntimeException $e) {
			return ApiResponse::error($e->getMessage(), 400);
		} catch (\Exception $e) {
			return ApiResponse::error('Internal server error', 500);
		}
	}

	/**
	 * @OA\Delete(
	 *     path="/country_kpas/{id}",
	 *     tags={"Country-KPAs"},
	 *     summary="Eliminar relación Country-KPA",
	 *     description="Desasocia un KPA de un país eliminando el registro de la relación",
	 *     security={{"bearerAuth":{}}},
	 *     @OA\Parameter(
	 *         name="id",
	 *         in="path",
	 *         required=true,
	 *         description="ID de la relación country_kpa a eliminar",
	 *         @OA\Schema(type="integer", example=1)
	 *     ),
	 *     @OA\Response(
	 *         response=200,
	 *         description="Eliminado exitosamente",
	 *         @OA\JsonContent(
	 *             @OA\Property(property="success", type="boolean", example=true),
	 *             @OA\Property(property="message", type="string", example="Eliminado"),
	 *             @OA\Property(property="data", type="object", description="Registro eliminado")
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=500,
	 *         description="Error interno del servidor",
	 *         @OA\JsonContent(
	 *             @OA\Property(property="success", type="boolean", example=false),
	 *             @OA\Property(property="message", type="string", example="Error al eliminar")
	 *         )
	 *     )
	 * )
	 */
	public function destroy($id): JsonResponse
	{
		try {
			// Verify user has access to the country before deleting
			$countryKpa = $this->service->getById($id);
			if (isset($countryKpa->id_country) && !$this->service->userHasAccessToCountry($countryKpa->id_country)) {
				return ApiResponse::error('Access denied to this country', 403);
			}
			
			$deleted = $this->service->delete((int)$id);
			return ApiResponse::success('Deleted', 200, $deleted);
		} catch (RuntimeException $e) {
			return ApiResponse::error($e->getMessage(), 500);
		}
	}

	/**
	 * @OA\Get(
	 *     path="/country_kpas/country/{id}",
	 *     tags={"Country-KPAs"},
	 *     summary="Obtener KPAs por país",
	 *     description="Obtiene todas las relaciones Country-KPA asociadas a un país específico",
	 *     security={{"bearerAuth":{}}},
	 *     @OA\Parameter(
	 *         name="id",
	 *         in="path",
	 *         required=true,
	 *         description="ID del país",
	 *         @OA\Schema(type="integer", example=1)
	 *     ),
	 *     @OA\Response(
	 *         response=200,
	 *         description="Relaciones obtenidas exitosamente",
	 *         @OA\JsonContent(
	 *             @OA\Property(property="success", type="boolean", example=true),
	 *             @OA\Property(
	 *                 property="message",
	 *                 type="string",
	 *                 example="Registration obtained"
	 *             ),
	 *             @OA\Property(
	 *                 property="data",
	 *                 type="array",
	 *                 @OA\Items(ref="#/components/schemas/CountryKpa")
	 *             )
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=404,
	 *         description="País sin KPAs asociados",
	 *         @OA\JsonContent(
	 *             @OA\Property(property="success", type="boolean", example=false),
	 *             @OA\Property(
	 *                 property="message",
	 *                 type="string",
	 *                 example="CountryKpa no encontrado"
	 *             )
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=500,
	 *         description="Error interno del servidor",
	 *         @OA\JsonContent(
	 *             @OA\Property(property="success", type="boolean", example=false),
	 *             @OA\Property(property="message", type="string")
	 *         )
	 *     )
	 * )
	 */
	public function showForCountry(Request $request, $id): JsonResponse
	{
		try {
			// Verify user has access to this country
			if (!$this->service->userCanViewCountryDashboard((int)$id)) {
				return ApiResponse::error('Access denied to this country', 403);
			}
			
			$search = $request->get("search");
			$perPage = (int) $request->get("per_page", 10);
			$paginator = $this->service->getKpasByCountryPaginated((int)$id, $search, $perPage);
        	if ($paginator->isEmpty()) return ApiResponse::notFound('CountryKpa');
        	$country = $paginator->first()->country;

			return ApiResponse::success('Register obtained', 200, [
				'country' => [
					'id' => $country->id,
					'name' => $country->name,
					'currency_id' => $country->currency_id,
					'active' => (bool) $country->active,
				],

				'kpas' => CountryKpaResource::collection($paginator),

				'pagination' => [
					'current_page' => $paginator->currentPage(),
					'last_page' => $paginator->lastPage(),
					'per_page' => $paginator->perPage(),
					'total' => $paginator->total(),
				],
			]);

		} catch (RuntimeException $e) {
			return ApiResponse::notFound('CountryKpa');
		} catch (\Exception $e) {
			return ApiResponse::error('Internal server error', 500);
		}
	}
}
