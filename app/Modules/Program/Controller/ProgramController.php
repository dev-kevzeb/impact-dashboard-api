<?php

namespace App\Modules\Program\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramRequest;
use App\Modules\Program\Service\ProgramService;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\ProgramResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
     *     summary="List all programs",
     *     description="Retrieves the complete list of programs with all their relationships loaded",
     *     @OA\Response(
     *         response=200,
     *         description="Program list retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Program list retrieved successfully"),
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
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error retrieving program list")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get("per_page", 10);
            $programs = $this->programService->getAllPrograms($perPage);

            return ApiResponse::success(
                'Programs paginated list successfully uploaded',
                200,
                [
                    'programs' => ProgramResource::collection($programs),
                    'total' => $programs->count(),
                    'per_page' => $programs->perPage(),
                    'current_page' => $programs->currentPage(),
                    'last_page' => $programs->lastPage(),
                ]
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Error retrieving program list', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/programs",
     *     tags={"Programs"},
     *     summary="Create new program",
     *     description="Creates a new program with 'Inactive' state by default (business rule). To change state, use PUT. **Arrays:** Use `sdg_ids[]=2&sdg_ids[]=5` or in form-data: `sdg_ids[0]=2, sdg_ids[1]=5`",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "description", "contact_id"},
     *           @OA\Property(property="name", type="string", maxLength=255, example="Rural Education Program 2025"),
     *             @OA\Property(property="description", type="string", maxLength=2000, example="Program focused on improving education in rural areas through teacher training and equipment."),
     *             @OA\Property(property="banner_img", type="string", format="binary", description="Program banner image (OPTIONAL - JPG, PNG, GIF, WEBP - max 2MB)"),
     *             @OA\Property(property="program_url", type="string", format="url", example="https://www.education-program.org", description="Program website URL (optional)"),
     *             @OA\Property(property="contact_id", type="integer", example=1, description="Responsible contact ID (required). Program is automatically created with 'Inactive' state."),
     *                 @OA\Property(
     *                     property="sdg_ids[]",
     *                     type="array",
     *                     @OA\Items(type="integer"),
     *                     example={2, 4, 13},
     *                     description="Array of SDG IDs (optional). Use: sdg_ids[0]=2, sdg_ids[1]=4, sdg_ids[2]=13"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Program created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Program created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Program")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Business validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The name must be at least 3 characters long")
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
     *                 @OA\Property(property="name", type="array", @OA\Items(type="string", example="A program with this name already exists."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
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
                $validated['contact'],
                $validated['sdg_ids'] ?? []
            );

            return ApiResponse::created(
                'Program created successfully',
                new ProgramResource($program)
            );
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'already exists')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error creating program: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/programs/search",
     *     tags={"Programs"},
     *     summary="Search program by name",
     *     description="Searches for a program by its exact name (case-insensitive)",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Exact program name to search",
     *         @OA\Schema(type="string", example="Rural Education Program 2025")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Program found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Program found"),
     *             @OA\Property(property="data", ref="#/components/schemas/Program")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Name parameter required",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The name parameter is required")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Program not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Program not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Search error")
     *         )
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $name = $request->query('name');

            if (empty($name)) {
                return ApiResponse::error('The name parameter is required', 400);
            }

            $program = $this->programService->findProgramByName($name);

            return ApiResponse::success(
                'Program found',
                200,
                new ProgramResource($program)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Program');
        } catch (\Exception $e) {
            return ApiResponse::error('Search error', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/programs/{id}",
     *     tags={"Programs"},
     *     summary="Get specific program",
     *     description="Retrieves complete details of a program by its ID, including all its relationships",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Program ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Program found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Program found"),
     *             @OA\Property(property="data", ref="#/components/schemas/Program")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Program not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Program not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error retrieving program")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $program = $this->programService->getProgramById($id);
            return ApiResponse::success(
                'Program found',
                200,
                new ProgramResource($program)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Program');
        } catch (\Exception $e) {
            return ApiResponse::error('Error retrieving program', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/programs/{id}",
     *     tags={"Programs"},
     *     summary="Update program",
     *     description="Updates an existing program, including state changes. Use POST with _method=PUT to send files from Postman",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Program ID to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "description", "contact_id", "program_state_id"},
     *                 @OA\Property(property="_method", type="string", example="PUT", description="HTTP method spoofing (required in Postman with form-data)"),
     *                 @OA\Property(property="name", type="string", maxLength=255, example="Rural Education Program 2025 - Updated"),
     *                 @OA\Property(property="description", type="string", maxLength=2000, example="Updated program description"),
     *                 @OA\Property(property="banner_img", type="string", format="binary", description="New banner image (optional, if not sent keeps current)"),
     *                 @OA\Property(property="program_url", type="string", format="url", example="https://www.updated-program.org"),
     *                 @OA\Property(property="contact_id", type="integer", example=3, description="Responsible contact ID (required)"),
     *                 @OA\Property(property="program_state_id", type="integer", example=2, description="Program state ID (required): 1=Inactive, 2=Active, 3=Completed"),
     *                 @OA\Property(property="sdg_ids", type="array", @OA\Items(type="integer", example=1), description="SDG IDs (replaces existing)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Program updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Program updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Program")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Business validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Program not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Technical validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error updating program")
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
                // Keep current banner
                $currentProgram = $this->programService->getProgramById($id);
                $bannerPath = $currentProgram->banner_img;
            }

            $program = $this->programService->updateProgram(
                $id,
                $validated['name'],
                $validated['description'],
                $bannerPath,
                $validated['program_url'] ?? '',
                $validated['contact'],
                $validated['program_state_id'],
                $validated['sdg_ids'] ?? []
            );

            return ApiResponse::success(
                'Program updated successfully',
                200,
                new ProgramResource($program)
            );
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'already exists')) {
                return ApiResponse::validationError(['name' => [$e->getMessage()]]);
            }
            if (str_contains($e->getMessage(), 'not found')) {
                return ApiResponse::notFound('Program');
            }
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error updating program', 500);
        }
    }
}
