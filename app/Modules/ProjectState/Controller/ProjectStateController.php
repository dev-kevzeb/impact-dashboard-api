<?php
namespace App\Modules\ProjectState\Controller;
use App\Http\Requests\ProjectStateRequest;
use App\Http\Resources\ProjectStateResource;
use App\Modules\ProjectState\Domain\ProjectState;
use App\Modules\ProjectState\Service\ProjectStateService;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ProjectStateController extends Controller
{
    private ProjectStateService $projectStateService;

    public function __construct(ProjectStateService $projectStateService)
    {
        $this->projectStateService = $projectStateService;
    }

    public function index(Request $request)
    {
        try {
            $search = $request->get("search");
            $perPage = (int) $request->get("per_page", 10);

            $query = ProjectState::query();
            if( $search ) $query->where("state","like","%". $search ."%");
            $states = $query->paginate($perPage);

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

    public function show($id)
    {
        try {
            $state = $this->projectStateService->getProjectStateById($id);
            return ApiResponse::success(
                'Project State found',
                200,
                new ProjectStateResource($state),
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Project State');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function store(ProjectStateRequest $request)
    {
        try {
            $validated = $request->validated();
            $state = $this->projectStateService->createProjectState($validated['state']);
            return ApiResponse::success(
                'Project State created successfully',
                201,
                new ProjectStateResource($state)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function update(ProjectStateRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $state = $this->projectStateService->updateProjectState($id, $validated['state']);
            return ApiResponse::success(
                'Project State uploaded successfully',
                200,
                new ProjectStateResource($state)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

}