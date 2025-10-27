<?php

namespace App\Modules\Beneficiary\Controller;

use App\Http\Controllers\Controller;
use App\Modules\Beneficiary\Service\BeneficiaryService;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\BeneficiaryResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class BeneficiaryController extends Controller
{
    private BeneficiaryService $beneficiaryService;

    public function __construct(BeneficiaryService $beneficiaryService)
    {
        $this->beneficiaryService = $beneficiaryService;
    }

    /**
     * Listar todos los beneficiarios
     */
    public function index(): JsonResponse
    {
        try {
            $beneficiaries = $this->beneficiaryService->getAllBeneficiaries();
            
            return ApiResponse::success(
                'Lista de beneficiarios obtenida exitosamente',
                200,
                [
                    'beneficiaries' => BeneficiaryResource::collection($beneficiaries),
                    'total' => $beneficiaries->count()
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Crear un nuevo beneficiario
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Solo validar que el name esté presente
            $request->validate([
                'name' => 'required|string'
            ]);

            $beneficiary = $this->beneficiaryService->createBeneficiary($request->input('name'));

            return ApiResponse::created(
                'Beneficiario creado exitosamente',
                new BeneficiaryResource($beneficiary)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * Mostrar un beneficiario específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $beneficiary = $this->beneficiaryService->getBeneficiaryById($id);

            return ApiResponse::success(
                'Beneficiario encontrado',
                200,
                new BeneficiaryResource($beneficiary)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Beneficiario');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Actualizar un beneficiario existente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Solo validar que el name esté presente
            $request->validate([
                'name' => 'required|string'
            ]);

            $beneficiary = $this->beneficiaryService->updateBeneficiary($id, $request->input('name'));

            return ApiResponse::success(
                'Beneficiario actualizado exitosamente',
                200,
                new BeneficiaryResource($beneficiary)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * Buscar beneficiario por nombre
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $beneficiary = $this->beneficiaryService->findBeneficiaryByName($request->input('name'));

            return ApiResponse::success(
                'Beneficiario encontrado',
                200,
                new BeneficiaryResource($beneficiary)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Beneficiario');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }
}
