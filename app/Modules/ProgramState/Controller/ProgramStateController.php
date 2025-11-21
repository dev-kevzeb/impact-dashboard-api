<?php

namespace App\Modules\ProgramState\Controller;

use App\Http\Controllers\Controller;
use App\Modules\ProgramState\Service\ProgramStateService;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\ProgramStateResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ProgramStateController extends Controller
{
    private ProgramStateService $programStateService;

    public function __construct(ProgramStateService $programStateService)
    {
        $this->programStateService = $programStateService;
    }

    /**
     * Listar todos los estados
     */
    public function index(): JsonResponse
    {
        try {
            $states = $this->programStateService->getAllProgramStates();
            return ApiResponse::success(
                'Lista de estados obtenida exitosamente',
                200,
                [
                    'program_states' => ProgramStateResource::collection($states),
                    'total' => $states->count()
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Mostrar un estado específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $state = $this->programStateService->getProgramStateById($id);
            return ApiResponse::success(
                'Estado encontrado',
                200,
                new ProgramStateResource($state)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Estado');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * Crear un nuevo estado
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string'
            ]);
            $state = $this->programStateService->createProgramState($request->input('name'));
            return ApiResponse::created(
                'Estado creado exitosamente',
                new ProgramStateResource($state)
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
     * Actualizar un estado existente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string'
            ]);
            $state = $this->programStateService->updateProgramState($id, $request->input('name'));
            return ApiResponse::success(
                'Estado actualizado exitosamente',
                200,
                new ProgramStateResource($state)
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
     * Buscar estado por nombre
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|min:1'
            ]);
            $state = $this->programStateService->findProgramStateByName($request->input('name'));
            return ApiResponse::success(
                'Estado encontrado',
                200,
                new ProgramStateResource($state)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Estado');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }
}
