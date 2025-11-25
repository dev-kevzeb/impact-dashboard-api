<?php

namespace App\Modules\Project\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Responses\ApiResponse;
use App\Modules\Project\Service\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    private ProjectService $projectService;
    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index()
    {
        try {
            $projects = $this->projectService->getAllProjects();

            $projects->load("donors");

            return ApiResponse::success(
                'Lista de proyectos obtenida exitosamente',
                200,
                [
                    'projects' => ProjectResource::collection($projects),
                    'total' => count($projects)
                ]
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function show($id)
    {
        try {
            $project = $this->projectService->findProjectById($id);

            return ApiResponse::success(
                'Proyecto encontrado',
                200,
                new ProjectResource($project)
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::notFound('Proyecto');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function store(ProjectRequest $request)
    {
        try {
            $validated= $request->validated();

            $project = $this->projectService->createProject($validated['name'], $validated['description'], $validated['project_url'] ?? null, $validated['start_date'], $validated['end_date'], $validated['progress'], $validated['comments'], $validated['project_budget'], $validated['contact_id'], $validated['beneficiary_id'], $validated['project_state_id']);
            return ApiResponse::created(
                'Proyecto creado exitosamente',
                new ProjectResource($project)
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function update(ProjectRequest $request, int $id)
    {
        try {
            $validated= $request->validated();
            $project = $this->projectService->updateProject($id, $validated['name'], $validated['description'], $validated['project_url'] ?? null, $validated['start_date'], $validated['end_date'], $validated['progress'], $validated['comments'], $validated['project_budget'], $validated['contact_id'], $validated['beneficiary_id'], $validated['project_state_id']);
            return ApiResponse::success(
                'Proyecto actualizado exitosamente',
                200,
                new ProjectResource($project)
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function search(Request $request)
    {
        try{
            $request->validate([
                'name' => 'required|string|min:1',
            ]);

            $project = $this->projectService->getProjectByName($request->input('name'));

            return ApiResponse::success(
                'Proyecto encontrado',
                200,
                new ProjectResource($project)
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::notFound('Project');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }
}