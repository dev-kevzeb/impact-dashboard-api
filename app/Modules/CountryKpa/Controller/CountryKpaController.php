<?php

namespace App\Modules\CountryKpa\Controller;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use App\Modules\CountryKpa\Service\CountryKpaService;
use RuntimeException;

class CountryKpaController extends Controller
{
	protected CountryKpaService $service;

	public function __construct(CountryKpaService $service)
	{
		$this->service = $service;
	}

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


