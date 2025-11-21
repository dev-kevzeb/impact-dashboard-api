<?php

namespace App\Modules\Beneficiary\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\BeneficiaryRequest;
use App\Modules\Beneficiary\Service\BeneficiaryService;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\BeneficiaryResource;
use Illuminate\Database\QueryException;
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
    public function store(BeneficiaryRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $beneficiary = $this->beneficiaryService->createBeneficiary($validated['name']);

            return ApiResponse::created(
                'Beneficiario creado exitosamente',
                new BeneficiaryResource($beneficiary)
            );
        } catch (QueryException $e) {
            // Error 23505 = Unique violation en PostgreSQL
            if ($e->getCode() == 23505 || $e->getCode() === '23505') {
                return ApiResponse::validationError(['name' => ['Este beneficiario ya existe en el sistema.']]);
            }
            return ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
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
    public function update(BeneficiaryRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $beneficiary = $this->beneficiaryService->updateBeneficiary($id, $validated['name']);

            return ApiResponse::success(
                'Beneficiario actualizado exitosamente',
                200,
                new BeneficiaryResource($beneficiary)
            );

        } catch (QueryException $e) {
            // Error 23505 = Unique violation en PostgreSQL
            if ($e->getCode() == 23505 || $e->getCode() === '23505') {
                return ApiResponse::validationError(['name' => ['Este beneficiario ya existe en el sistema.']]);
            }
            return ApiResponse::error('Error de base de datos', 500);
        } catch (RuntimeException $e) {
            // Si el mensaje indica que no se encontró, retornar 404
            if (str_contains($e->getMessage(), 'no encontrado')) {
                return ApiResponse::notFound('Beneficiario');
            }
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            // Otros errores de negocio retornan 400
            return ApiResponse::error($e->getMessage(), 400);
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
