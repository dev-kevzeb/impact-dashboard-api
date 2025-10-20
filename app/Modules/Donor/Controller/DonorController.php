<?php

namespace App\Modules\Donor\Controller;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\DonorResource;
use App\Modules\Donor\Service\DonorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class DonorController extends Controller
{
    public function __construct(
        private readonly DonorService $donorService
    ) {}

    /**
     * Listar todos los donantes
     */
    public function index(): JsonResponse
    {
        try {
            $donors = $this->donorService->getAllDonors();
            
            return ApiResponse::success(
                'Lista de donantes obtenida exitosamente',
                200,
                [
                    'donors' => $donors,
                    'total' => count($donors)
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Mostrar un donante específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $donor = $this->donorService->getDonorById($id);

            return ApiResponse::success(
                'Donante encontrado',
                200,
                new DonorResource($donor)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Donante');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Crear un nuevo donante
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Solo validar que el name esté presente
            $request->validate([
                'name' => 'required|string'
            ]);

            $donor = $this->donorService->createDonor($request->input('name'));

            return ApiResponse::created(
                'Donante creado exitosamente',
                new DonorResource($donor)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * Actualizar un donante existente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Solo validar que el name esté presente
            $request->validate([
                'name' => 'required|string'
            ]);

            $donor = $this->donorService->updateDonor($id, $request->input('name'));

            return ApiResponse::success(
                'Donante actualizado exitosamente',
                200,
                new DonorResource($donor)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * Buscar donante por nombre
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $donor = $this->donorService->findDonorByName($request->input('name'));

            return ApiResponse::success(
                'Donante encontrado',
                200,
                new DonorResource($donor)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Donante');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Obtener estadísticas de donantes
     */
    public function stats(): JsonResponse
    {
        try {
            $total = $this->donorService->getTotalDonors();

            return ApiResponse::success(
                'Estadísticas de donantes obtenidas',
                200,
                [
                    'total_donors' => $total
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }
}
