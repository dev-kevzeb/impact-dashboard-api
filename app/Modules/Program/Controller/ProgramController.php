<?php

namespace App\Modules\Program\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramRequest;
use App\Modules\Program\Service\ProgramService;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\ProgramResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ProgramController extends Controller
{
    private ProgramService $programService;

    public function __construct(ProgramService $programService)
    {
        $this->programService = $programService;
    }

    /**
     * GET /api/v1/programs
     * Listar todos los programas
     */
    public function index(): JsonResponse
    {
        try {
            $programs = $this->programService->getAllPrograms();
            return ApiResponse::success(
                'Lista de programas obtenida exitosamente',
                200,
                [
                    'programs' => ProgramResource::collection($programs),
                    'total' => $programs->count()
                ]
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener la lista de programas', 500);
        }
    }

    /**
     * POST /api/v1/programs
     * Crear nuevo programa
     */
    public function store(ProgramRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Manejar upload de imagen
            $file = $request->file('banner_img');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('program_banners', $filename, 'public');

            $program = $this->programService->createProgram(
                $validated['name'],
                $validated['description'],
                $path,  // Path guardado en storage
                $validated['start_date'],
                $validated['end_date'],
                $validated['program_url'] ?? '',
                $validated['contact_id'],
                $validated['beneficiary_id'],
                $validated['program_state_id'],
                $validated['country_id'],
                $validated['agency_id'],
                $validated['sdg_ids'] ?? [],
                $validated['donor_ids'] ?? []
            );

            return ApiResponse::created(
                'Programa creado exitosamente',
                new ProgramResource($program)
            );
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear el programa', 500);
        }
    }

    /**
     * GET /api/v1/programs/search?name={query}
     * Buscar programa por nombre
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $name = $request->query('name');

            if (empty($name)) {
                return ApiResponse::error('El parámetro name es requerido', 400);
            }

            $program = $this->programService->findProgramByName($name);

            return ApiResponse::success(
                'Programa encontrado',
                200,
                new ProgramResource($program)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Programa');
        } catch (\Exception $e) {
            return ApiResponse::error('Error en la búsqueda', 500);
        }
    }

    /**
     * GET /api/v1/programs/{id}
     * Obtener un programa específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $program = $this->programService->getProgramById($id);
            return ApiResponse::success(
                'Programa encontrado',
                200,
                new ProgramResource($program)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Programa');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener el programa', 500);
        }
    }

    /**
     * PUT /api/v1/programs/{id}
     * Actualizar programa
     */
    public function update(ProgramRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Manejar upload de imagen (opcional en update)
            $bannerPath = null;
            if ($request->hasFile('banner_img')) {
                $file = $request->file('banner_img');
                $filename = time() . '_' . $file->getClientOriginalName();
                $bannerPath = $file->storeAs('program_banners', $filename, 'public');
            } else {
                // Mantener el banner actual
                $currentProgram = $this->programService->getProgramById($id);
                $bannerPath = $currentProgram->banner_img;
            }

            $program = $this->programService->updateProgram(
                $id,
                $validated['name'],
                $validated['description'],
                $bannerPath,
                $validated['start_date'],
                $validated['end_date'],
                $validated['program_url'] ?? '',
                $validated['contact_id'],
                $validated['beneficiary_id'],
                $validated['program_state_id'],
                $validated['country_id'],
                $validated['agency_id'],
                $validated['sdg_ids'] ?? [],
                $validated['donor_ids'] ?? []
            );

            return ApiResponse::success(
                'Programa actualizado exitosamente',
                200,
                new ProgramResource($program)
            );
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            if (str_contains($e->getMessage(), 'no encontrado')) {
                return ApiResponse::notFound('Programa');
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar el programa', 500);
        }
    }
}
