<?php

namespace App\Modules\Donor\Controller;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\DonorResource;
use App\Http\Requests\DonorRequest;
use App\Modules\Donor\Service\DonorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class DonorController extends Controller
{
    private DonorService $donorService;

    public function __construct(DonorService $donorService)
    {
        $this->donorService = $donorService;
    }

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
                    'donors' => DonorResource::collection($donors),
                    'total' => $donors->count()
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
    public function store(DonorRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $donor = $this->donorService->createDonor($validated['name'], $validated['contribution'], $validated['project_id']);

            return ApiResponse::created(
                'Donante creado exitosamente',
                new DonorResource($donor)
            );
        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Actualizar un donante existente
     */
    public function update(DonorRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $donor = $this->donorService->updateDonor($id, $validated['name'], $validated['contribution'], $validated['project_id']);

            return ApiResponse::success(
                'Donante actualizado exitosamente',
                200,
                new DonorResource($donor)
            );
        } catch (RuntimeException $e) {
            // Si el error es "no encontrado", retornar 404
            if (str_contains($e->getMessage(), 'no encontrado')) {
                return ApiResponse::notFound('Donante');
            }
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
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
}