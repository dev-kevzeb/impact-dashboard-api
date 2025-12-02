<?php

namespace App\Modules\CountryKpa\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\CountryKpaRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use \Illuminate\Http\JsonResponse;
use App\Modules\CountryKpa\Service\CountryKpaService;
use RuntimeException;

class CountryKpaController extends Controller
{
	protected CountryKpaService $service;

	public function __construct(CountryKpaService $service)
	{
		$this->service = $service;
	}

	public function index(Request $request): JsonResponse
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

	public function show($id): JsonResponse
	{
		try {
			$countryKpa = $this->service->getById((int)$id);
			return ApiResponse::success('Registro obtenido', 200, $countryKpa);
		} catch (RuntimeException $e) {
			return ApiResponse::notFound('CountryKpa');
		} catch (\Exception $e) {
			return ApiResponse::error('Error interno del servidor', 500);
		}
	}
	public function store(CountryKpaRequest $request): JsonResponse
	{
		try {
			$validated = $request->validated();

			$created = $this->service->create($validated);

			return ApiResponse::created('Relación Country-KPA creada exitosamente', $created);

		} catch (\Illuminate\Validation\ValidationException $e) {
			return ApiResponse::validationError($e->errors());
		} catch (RuntimeException $e) {
			return ApiResponse::error($e->getMessage(), 400);
		} catch (\Exception $e) {
			return ApiResponse::error('Error interno del servidor', 500);
		}
	}

	public function update(CountryKpaRequest $request, int $id): JsonResponse
	{
		try {
			$validated = $request->validated();

			$updated = $this->service->update($id, $validated);

			return ApiResponse::success(
				'Relación Country-KPA actualizada exitosamente',
				200,
				$updated
			);
		} catch (\Illuminate\Validation\ValidationException $e) {
			return ApiResponse::validationError($e->errors());

		} catch (RuntimeException $e) {
			return ApiResponse::error($e->getMessage(), 400);

		} catch (\Exception $e) {
			return ApiResponse::error('Error interno del servidor', 500);
		}
	}


	public function destroy($id): JsonResponse
	{
		try {
			$deleted = $this->service->delete((int)$id);
			return ApiResponse::success('Eliminado', 200, $deleted);
		} catch (RuntimeException $e) {
			return ApiResponse::error($e->getMessage(), 500);
		}
	}


}