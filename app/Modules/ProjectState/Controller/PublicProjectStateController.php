<?php

namespace App\Modules\ProjectState\Controller;

use App\Http\Requests\ProjectStateRequest;
use App\Http\Resources\ProjectStateResource;
use App\Modules\ProjectState\Service\ProjectStateService;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use RuntimeException;


class PublicProjectStateController extends Controller
{
    private ProjectStateService $projectStateService;

    public function __construct(ProjectStateService $projectStateService)
    {
        $this->projectStateService = $projectStateService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->get("search");
            $perPage = (int) $request->get("per_page", 10);

            $states = $this->projectStateService->getProjectStatesPaginated($search, $perPage);

            return ApiResponse::success(
                'Project States list successfully obtained',
                200,
                [
                    'project_states' => ProjectStateResource::collection($states),
                    'total' => $states->count(),
                    'per_page' => $states->perPage(),
                    'current_page' => $states->currentPage(),
                    'last_page' => $states->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        } 
    }
}