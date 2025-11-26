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
     * @OA\Get(
     *     path="/programs",
     *     tags={"Programs"},
     *     summary="Listar todos los programas",
     *     description="Obtiene la lista completa de programas con todas sus relaciones cargadas",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de programas obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de programas obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="programs", type="array", @OA\Items(ref="#/components/schemas/Program")),
     *                 @OA\Property(property="total", type="integer", example=15)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al obtener la lista de programas")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/programs",
     *     tags={"Programs"},
     *     summary="Crear nuevo programa",
     *     description="Crea un nuevo programa con estado 'Inactivo' por defecto (regla de negocio). Para cambiar el estado, usar PUT. **Arrays:** Usa `sdg_ids[]=2&sdg_ids[]=5` o en form-data: `sdg_ids[0]=2, sdg_ids[1]=5`",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "description", "contact_id"},
     *                 @OA\Property(property="name", type="string", maxLength=255, example="Programa de Educación Rural 2025"),
     *                 @OA\Property(property="description", type="string", maxLength=2000, example="Programa enfocado en mejorar la educación en zonas rurales mediante capacitación docente y equipamiento."),
     *                 @OA\Property(property="banner_img", type="string", format="binary", description="Imagen banner del programa (OPCIONAL - JPG, PNG, GIF, WEBP - máx 2MB)"),
     *                 @OA\Property(property="program_url", type="string", format="url", example="https://www.programa-educacion.org", description="URL del sitio web del programa (opcional)"),
     *                 @OA\Property(property="contact_id", type="integer", example=1, description="ID del contacto responsable (requerido). El programa se crea automáticamente con estado 'Inactivo'."),
     *                 @OA\Property(
     *                     property="sdg_ids[]",
     *                     type="array",
     *                     @OA\Items(type="integer"),
     *                     example={2, 4, 13},
     *                     description="Array de IDs de ODS (opcional). Usar: sdg_ids[0]=2, sdg_ids[1]=4, sdg_ids[2]=13"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Programa creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Programa creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Program")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de negocio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="El nombre debe tener al menos 3 caracteres")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación técnica",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="name", type="array", @OA\Items(type="string", example="Ya existe un programa con este nombre."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function store(ProgramRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Manejar upload de imagen (opcional)
            $path = null;
            if ($request->hasFile('banner_img')) {
                $file = $request->file('banner_img');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('program_banners', $filename, 'public');
            }

            $program = $this->programService->createProgram(
                $validated['name'],
                $validated['description'],
                $path,  // Path guardado en storage
                $validated['program_url'] ?? '',
                $validated['contact_id'],
                $validated['sdg_ids'] ?? []
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
            return ApiResponse::error('Error al crear el programa: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/programs/search",
     *     tags={"Programs"},
     *     summary="Buscar programa por nombre",
     *     description="Busca un programa por su nombre exacto (case-insensitive)",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Nombre exacto del programa a buscar",
     *         @OA\Schema(type="string", example="Programa de Educación Rural 2025")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Programa encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Programa encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Program")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Parámetro name requerido",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="El parámetro name es requerido")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Programa no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Programa no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error en la búsqueda")
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/programs/{id}",
     *     tags={"Programs"},
     *     summary="Obtener un programa específico",
     *     description="Obtiene los detalles completos de un programa por su ID, incluyendo todas sus relaciones",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del programa",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Programa encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Programa encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Program")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Programa no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Programa no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al obtener el programa")
     *         )
     *     )
     * )
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
     * @OA\Put(
     *     path="/programs/{id}",
     *     tags={"Programs"},
     *     summary="Actualizar programa",
     *     description="Actualiza un programa existente, incluyendo cambios de estado. Usar POST con _method=PUT para enviar archivos desde Postman",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del programa a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "description", "contact_id", "program_state_id"},
     *                 @OA\Property(property="_method", type="string", example="PUT", description="Método HTTP spoofing (requerido en Postman con form-data)"),
     *                 @OA\Property(property="name", type="string", maxLength=255, example="Programa de Educación Rural 2025 - Actualizado"),
     *                 @OA\Property(property="description", type="string", maxLength=2000, example="Descripción actualizada del programa"),
     *                 @OA\Property(property="banner_img", type="string", format="binary", description="Nueva imagen banner (opcional, si no se envía mantiene la actual)"),
     *                 @OA\Property(property="program_url", type="string", format="url", example="https://www.programa-actualizado.org"),
     *                 @OA\Property(property="contact_id", type="integer", example=3, description="ID del contacto responsable (requerido)"),
     *                 @OA\Property(property="program_state_id", type="integer", example=2, description="ID del estado del programa (requerido): 1=Inactivo, 2=Activo, 3=Finalizado"),
     *                 @OA\Property(property="sdg_ids", type="array", @OA\Items(type="integer", example=1), description="IDs de ODS (reemplaza los existentes)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Programa actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Programa actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Program")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de negocio"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Programa no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación técnica"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al actualizar el programa")
     *         )
     *     )
     * )
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
                $validated['program_url'] ?? '',
                $validated['contact_id'],
                $validated['program_state_id'],
                $validated['sdg_ids'] ?? []
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
