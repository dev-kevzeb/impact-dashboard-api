<?php

namespace App\Modules\Agency\Controller;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\AgencyResource;
use App\Modules\Agency\Service\AgencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AgencyController extends Controller
{
    private AgencyService $agencyService;

    public function __construct(AgencyService $agencyService)
    {
        $this->agencyService = $agencyService;
    }

    /**
     * Listar todas las agencias
     */
    public function index(): JsonResponse
    {
        try {
            $agencies = $this->agencyService->getAllAgencies();
            
            return ApiResponse::success(
                'Lista de agencias obtenida exitosamente',
                200,
                [
                    'agencies' => AgencyResource::collection($agencies),
                    'total' => $agencies->count()
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Mostrar una agencia específica
     */
    public function show(int $id): JsonResponse
    {
        try {
            $agency = $this->agencyService->getAgencyById($id);

            return ApiResponse::success(
                'Agencia encontrada',
                200,
                new AgencyResource($agency)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Agencia');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Crear una nueva agencia
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validar campos requeridos
            $request->validate([
                'name' => 'required|string',
                'url' => 'required|string',
                'is_approved' => 'required|boolean'
            ]);

            $agency = $this->agencyService->createAgency(
                $request->input('name'),
                $request->input('url'),
                $request->input('is_approved')
            );

            return ApiResponse::created(
                'Agencia creada exitosamente',
                new AgencyResource($agency)
            );

        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * Actualizar una agencia existente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Validar campos requeridos
            $request->validate([
                'name' => 'required|string',
                'url' => 'required|string',
                'is_approved' => 'required|boolean'
            ]);

            $agency = $this->agencyService->updateAgency(
                $id,
                $request->input('name'),
                $request->input('url'),
                $request->input('is_approved')
            );

            return ApiResponse::success(
                'Agencia actualizada exitosamente',
                200,
                new AgencyResource($agency)
            );

        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * Buscar agencia por nombre
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $agency = $this->agencyService->findAgencyByName($request->input('name'));

            return ApiResponse::success(
                'Agencia encontrada',
                200,
                new AgencyResource($agency)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Agencia');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }
}
