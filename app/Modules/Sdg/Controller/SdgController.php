<?php

namespace App\Modules\Sdg\Controller;

use App\Http\Controllers\Controller;
use App\Modules\Sdg\Service\SdgService;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\SdgResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class SdgController extends Controller
{
    private SdgService $sdgService;

    public function __construct(SdgService $sdgService)
    {
        $this->sdgService = $sdgService;
    }

    /**
     * Listar todos los SDGs
     */
    public function index(): JsonResponse
    {
        try {
            $sdgs = $this->sdgService->getAllSdgs();
            return ApiResponse::success(
                'Lista de SDGs obtenida exitosamente',
                200,
                [
                    'sdgs' => SdgResource::collection($sdgs),
                    'total' => $sdgs->count()
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Mostrar un SDG específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $sdg = $this->sdgService->getSdgById($id);
            return ApiResponse::success(
                'SDG encontrado',
                200,
                new SdgResource($sdg)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('SDG');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Crear un nuevo SDG
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'image' => 'required|string'
            ]);
            $sdg = $this->sdgService->createSdg($request->input('image'));
            return ApiResponse::created(
                'SDG creado exitosamente',
                new SdgResource($sdg)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * Actualizar un SDG existente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'image' => 'required|string'
            ]);
            $sdg = $this->sdgService->updateSdg($id, $request->input('image'));
            return ApiResponse::success(
                'SDG actualizado exitosamente',
                200,
                new SdgResource($sdg)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * Buscar SDG por nombre
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'image' => 'required|string|min:1'
            ]);
            $sdg = $this->sdgService->findSdgByImage($request->input('image'));
            return ApiResponse::success(
                'SDG encontrado',
                200,
                new SdgResource($sdg)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('SDG');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }
}
