<?php

namespace App\Modules\Kpa\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\KpaRequest;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\KpaResource;
use App\Modules\Kpa\Service\KpaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class KpaController extends Controller
{
    private KpaService $kpaService;

    public function __construct(KpaService $kpaService)
    {
        $this->kpaService = $kpaService;
    }

    /**
     * Listar todos los KPAs
     */
    public function index(): JsonResponse
    {
        try {
            $kpas = $this->kpaService->getAllKpas();
            
            return ApiResponse::success(
                'Lista de KPAs obtenida exitosamente',
                200,
                [
                    'kpas' => KpaResource::collection($kpas),
                    'total' => $kpas->count()
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Mostrar un KPA específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $kpa = $this->kpaService->getKpaById($id);

            return ApiResponse::success(
                'KPA encontrado',
                200,
                new KpaResource($kpa)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('KPA');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Crear un nuevo KPA
     */
    public function store(KpaRequest $request): JsonResponse
    {
        try {
            // Validar que los campos estén presentes
            $validated = $request->validated();

            $kpa = $this->kpaService->createKpa(
                $validated['name'],
                (float) $validated['implementation']
            );

            return ApiResponse::created(
                'KPA creado exitosamente',
                new KpaResource($kpa)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * Actualizar un KPA existente
     */
    public function update(KpaRequest $request, int $id): JsonResponse
    {
        try {
            // Validar que los campos estén presentes
            $validated = $request->validated();

            $kpa = $this->kpaService->updateKpa(
                $id,
                $validated['name'],
                (float) $validated['implementation']
            );

            return ApiResponse::success(
                'KPA actualizado exitosamente',
                200,
                new KpaResource($kpa)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * Buscar KPA por nombre
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $kpa = $this->kpaService->findKpaByName($request->input('name'));

            return ApiResponse::success(
                'KPA encontrado',
                200,
                new KpaResource($kpa)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::notFound('KPA');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }
}
