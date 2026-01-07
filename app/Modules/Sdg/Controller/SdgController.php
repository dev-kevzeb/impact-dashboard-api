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
 *     description="UN Sustainable Development Goals model",
 *     @OA\Property(property="id", type="integer", example=1, description="Unique SDG ID"),
 *     @OA\Property(property="image", type="string", example="sdg_images/1732567890_sdg-01.png", description="Image storage path"),
 *     @OA\Property(property="filename", type="string", example="sdg-01.png", description="Original image filename")
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
     *     summary="List all SDGs",
     *     description="Retrieves the complete list of UN Sustainable Development Goals (SDGs) with their images",
     *     @OA\Response(
     *         response=200,
     *         description="List retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="SDG list retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="sdgs",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Sdg")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=17, description="Total available SDGs")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
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
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/sdgs/{id}",
     *     tags={"SDGs"},
     *     summary="Get specific SDG",
     *     description="Retrieves detailed information of a Sustainable Development Goal by its ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="SDG ID to retrieve",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SDG found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="SDG found"),
     *             @OA\Property(property="data", ref="#/components/schemas/Sdg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="SDG not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="SDG not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $sdg = $this->sdgService->getSdgById($id);
            return ApiResponse::success(
                'SDG found',
                200,
                new SdgResource($sdg)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('SDG');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/sdgs",
     *     tags={"SDGs"},
     *     summary="Create new SDG",
     *     description="Uploads an SDG image (Sustainable Development Goal). The system validates that no image with the same name exists.",
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
     *                     description="SDG image file (JPG, PNG, GIF, WEBP, SVG - max 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="SDG created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="SDG created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Sdg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Domain validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="the file must have an extension (examples: .jpg, .png, .gif, .webp, .svg)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Technical validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="image",
     *                     type="array",
     *                     @OA\Items(type="string", example="The image field is required.")
     *                 ),
     *                 @OA\Property(
     *                     property="filename",
     *                     type="array",
     *                     @OA\Items(type="string", example="An SDG with the name already exists: sdg-01.png")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
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
                'SDG created successfully',
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
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/sdgs/{id}",
     *     tags={"SDGs"},
     *     summary="Update existing SDG",
     *     description="Updates the image of an existing SDG. Use POST with _method=PUT to send files from Postman",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="SDG ID to update",
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
     *         description="SDG updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="SDG updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Sdg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Domain validation error or SDG not found"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="SDG not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Technical validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="filename",
     *                     type="array",
     *                     @OA\Items(type="string", example="An SDG with the name already exists: sdg-02.png")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
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
                'SDG updated successfully',
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
            return ApiResponse::error('Internal server error', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/sdgs/search",
     *     tags={"SDGs"},
     *     summary="Search SDG by filename",
     *     description="Searches for a specific SDG by its filename",
     *     @OA\Parameter(
     *         name="filename",
     *         in="query",
     *         required=true,
     *         description="Filename to search (can be partial)",
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
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="filename",
     *                     type="array",
     *                     @OA\Items(type="string", example="The filename field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
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
                'SDG found',
                200,
                new SdgResource($sdg)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('SDG');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
