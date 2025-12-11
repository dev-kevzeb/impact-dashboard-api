<?php

namespace App\Modules\Sdg\Controller;

use App\Http\Controllers\Controller;
use App\Modules\Sdg\Service\SdgService;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\SdgResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * @OA\Schema(
 *     schema="Sdg",
 *     type="object",
 *     title="Sdg",
 *     description="Modelo de Objetivo de Desarrollo Sostenible de la ONU",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del SDG"),
 *     @OA\Property(property="image", type="string", example="sdg_images/1732567890_sdg-01.png", description="Ruta de almacenamiento de la imagen"),
 *     @OA\Property(property="filename", type="string", example="sdg-01.png", description="Nombre original del archivo de imagen")
 * )
 */
class SdgController extends Controller
{
    private SdgService $sdgService;

    public function __construct(SdgService $sdgService)
    {
        $this->sdgService = $sdgService;
    }

    /**
     * @OA\Get(
     *     path="/sdgs",
     *     tags={"SDGs"},
     *     summary="Listar todos los SDGs",
     *     description="Obtiene la lista completa de Objetivos de Desarrollo Sostenible (SDGs) de la ONU con sus imágenes",
     *     @OA\Response(
     *         response=200,
     *         description="Lista obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de SDGs obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="sdgs",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Sdg")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=17, description="Total de SDGs disponibles")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get("per_page", 10);
            $sdgs = $this->sdgService->getAllSdgs($perPage);
            
            return ApiResponse::success(
                'SDGs paginated list successfully uploaded',
                200,
                [
                    'sdgs' => SdgResource::collection($sdgs),
                    'total' => $sdgs->count(),
                    'per_page' => $sdgs->perPage(),
                    'current_page' => $sdgs->currentPage(),
                    'last_page' => $sdgs->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/sdgs/{id}",
     *     tags={"SDGs"},
     *     summary="Obtener un SDG específico",
     *     description="Obtiene la información detallada de un Objetivo de Desarrollo Sostenible por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del SDG a obtener",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SDG encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="SDG encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Sdg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="SDG no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="SDG no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
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
     * @OA\Post(
     *     path="/sdgs",
     *     tags={"SDGs"},
     *     summary="Crear nuevo SDG",
     *     description="Sube una imagen de SDG (Objetivo de Desarrollo Sostenible). El sistema valida que no exista una imagen con el mismo nombre.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"image"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Archivo de imagen del SDG (JPG, PNG, GIF, WEBP, SVG - máx 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="SDG creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="SDG creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Sdg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="el archivo debe tener una extensión (ejemplos: .jpg, .png, .gif, .webp, .svg)")
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
     *                 @OA\Property(
     *                     property="image",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo imagen es obligatorio.")
     *                 ),
     *                 @OA\Property(
     *                     property="filename",
     *                     type="array",
     *                     @OA\Items(type="string", example="Ya existe un SDG con el nombre: sdg-01.png")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp,svg|max:2048'
            ]);

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('sdg_images', $filename, 'public');
            $filename = $file->getClientOriginalName();

            $sdg = $this->sdgService->createSdg($path, $filename);
            return ApiResponse::created(
                'SDG creado exitosamente',
                new SdgResource($sdg)
            );
        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['filename' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/sdgs/{id}",
     *     tags={"SDGs"},
     *     summary="Actualizar SDG existente",
     *     description="Actualiza la imagen de un SDG existente. Usar POST con _method=PUT para enviar archivos desde Postman",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del SDG a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"image"},
     *                 @OA\Property(property="_method", type="string", example="PUT", description="Método HTTP spoofing (requerido en Postman con form-data)"),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Nueva imagen del SDG (JPG, PNG, GIF, WEBP, SVG - máx 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SDG actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="SDG actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Sdg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación de dominio o SDG no encontrado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="SDG no encontrado"
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
     *                 @OA\Property(
     *                     property="filename",
     *                     type="array",
     *                     @OA\Items(type="string", example="Ya existe un SDG con el nombre: sdg-02.png")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp,svg|max:2048'
            ]);

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('sdg_images', $filename, 'public');
            $filename = $file->getClientOriginalName();

            $sdg = $this->sdgService->updateSdg($id, $path, $filename);
            return ApiResponse::success(
                'SDG actualizado exitosamente',
                200,
                new SdgResource($sdg)
            );
        } catch (RuntimeException $e) {
            // Si el error es de duplicado, retornar como error de validación (422)
            if (str_contains($e->getMessage(), 'Ya existe')) {
                return ApiResponse::validationError(['filename' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/sdgs/search",
     *     tags={"SDGs"},
     *     summary="Buscar SDG por nombre de archivo",
     *     description="Busca un SDG específico por su nombre de archivo (filename)",
     *     @OA\Parameter(
     *         name="filename",
     *         in="query",
     *         required=true,
     *         description="Nombre del archivo a buscar (puede ser parcial)",
     *         @OA\Schema(type="string", example="sdg-01.png")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SDG encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="SDG encontrado"),
     *             @OA\Property(property="data", ref="#/components/schemas/Sdg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="SDG no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="SDG no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="filename",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo filename es obligatorio.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'filename' => 'required|string|min:1'
            ]);
            $sdg = $this->sdgService->findSdgByFilename($request->query('filename'));
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
