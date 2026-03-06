<?php

namespace App\Modules\Project\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\SimpleProjectResource;
use App\Http\Responses\ApiResponse;
use App\Modules\Project\Service\ProjectService;

class PublicProjectController extends Controller
{
    private ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index(ProjectRequest $request)
    {
        try
        {
            $search = $request->get("search");
            $per_page = (int) $request->get("per_page", 10);
            $sort = $request->get("sort", "date_newest");
            $validated = $request->validated();

            $projects = $this->projectService->getPublicProjects($validated, $search, $per_page, $sort);

            return ApiResponse::success(
                'Projects paginated of selected program list successfully uploaded',
                200,
                [
                    'projects' => SimpleProjectResource::collection($projects),
                    'total' => $projects->count(),
                    'per_page' => $projects->perPage(),
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                    'all' => $projects->total(),
                ]
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function show($id)
    {
        try {
            $project = $this->projectService->findProjectById($id);

            return ApiResponse::success(
                'Project Found',
                200,
                new ProjectResource($project)
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::notFound('Project');
        } catch (\Exception $e) {
            return ApiResponse::error($e, 500);
        }
    }
}
