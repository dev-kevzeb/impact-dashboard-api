<?php

namespace App\Modules\CountryKpa\Controller;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
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
	public function index(Request $request): \Illuminate\Http\JsonResponse
	{
		try {
			if ($request->has('country')) {
				$countryId = (int) $request->query('country');
				$countryWithKpas = $this->service->getCountryKpasByCountryId($countryId);
				return ApiResponse::success('KPAs del pais obtenidos', 200, $countryWithKpas);
			}
			
			$items = $this->service->getAll();
			return ApiResponse::success('Lista obtenida', 200, ['items' => $items, 'total' => count($items)]);
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
	public function show($id): \Illuminate\Http\JsonResponse
	{
		try {
			// Obtener por ID de la tabla country_kpa
			$countryKpa = $this->service->getById((int)$id);
			return ApiResponse::success('Registro obtenido', 200, $countryKpa);
		} catch (RuntimeException $e) {
			return ApiResponse::notFound('CountryKpa');
		} catch (\Exception $e) {
			return ApiResponse::error('Error interno del servidor', 500);
		}
	}
	/**
	 * @OA\Post(
	 *     path="/country_kpas",
	 *     tags={"Country-KPAs"},
	 *     summary="Crear nueva relación Country-KPA",
	 *     description="Asocia un KPA (Key Priority Area) a un país específico",
	 *     @OA\RequestBody(
	 *         required=true,
	 *         @OA\MediaType(
	 *             mediaType="application/json",
	 *             @OA\Schema(
	 *                 required={"id_country", "id_kpa"},
	 *                 @OA\Property(property="id_country", type="integer", example=1, description="ID del país (debe existir en country)"),
	 *                 @OA\Property(property="id_kpa", type="integer", example=2, description="ID del KPA (debe existir en kpa)")
	 *             )
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=201,
	 *         description="Creado exitosamente",
	 *         @OA\JsonContent(
	 *             @OA\Property(property="success", type="boolean", example=true),
	 *             @OA\Property(property="message", type="string", example="Creado exitosamente"),
	 *             @OA\Property(property="data", ref="#/components/schemas/CountryKpa")
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=400,
	 *         description="Error de validación de dominio (FK inválidas)",
	 *         @OA\JsonContent(
	 *             @OA\Property(property="success", type="boolean", example=false),
	 *             @OA\Property(property="message", type="string", example="El país o KPA no existen")
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
	 *                     property="id_country",
	 *                     type="array",
	 *                     @OA\Items(type="string", example="El campo id_country es obligatorio.")
	 *                 ),
	 *                 @OA\Property(
	 *                     property="id_kpa",
	 *                     type="array",
	 *                     @OA\Items(type="string", example="El campo id_kpa es obligatorio.")
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
	public function store(Request $request): \Illuminate\Http\JsonResponse
	{
		try {
			$request->validate(['id_country' => 'required|integer', 'id_kpa' => 'required|integer']);
			$data = $request->only(['id_country', 'id_kpa']);
			$created = $this->service->create($data);
			return ApiResponse::created('Creado exitosamente', $created);
		} catch (\Illuminate\Validation\ValidationException $e) {
			return ApiResponse::validationError($e->errors());
		} catch (RuntimeException $e) {
			return ApiResponse::error($e->getMessage(), 400);
		} catch (\Exception $e) {
			return ApiResponse::error('Error interno del servidor', 500);
		}
	}

	/**
	 * @OA\Put(
	 *     path="/country_kpas/{id}",
	 *     tags={"Country-KPAs"},
	 *     summary="Actualizar relación Country-KPA",
	 *     description="Actualiza una relación existente país-KPA (cambia el país o KPA asociado)",
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
	public function update(Request $request, $id): \Illuminate\Http\JsonResponse
	{
		try {
			$request->validate(['id_country' => 'required|integer', 'id_kpa' => 'required|integer']);
			$data = $request->only(['id_country', 'id_kpa']);
			$updated = $this->service->update((int)$id, $data);
			return ApiResponse::success('Actualizado exitosamente', 200, $updated);
		} catch (\Illuminate\Validation\ValidationException $e) {
			return ApiResponse::validationError($e->errors());
		} catch (RuntimeException $e) {
			return ApiResponse::error($e->getMessage(), 400);
		} catch (\Exception $e) {
			return ApiResponse::error('Error interno del servidor', 500);
		}
	}

	/**
	 * @OA\Delete(
	 *     path="/country_kpas/{id}",
	 *     tags={"Country-KPAs"},
	 *     summary="Eliminar relación Country-KPA",
	 *     description="Desasocia un KPA de un país eliminando el registro de la relación",
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
	public function destroy($id): \Illuminate\Http\JsonResponse
	{
		try {
			$deleted = $this->service->delete((int)$id);
			return ApiResponse::success('Eliminado', 200, $deleted);
		} catch (RuntimeException $e) {
			return ApiResponse::error($e->getMessage(), 500);
		}
	}


}


