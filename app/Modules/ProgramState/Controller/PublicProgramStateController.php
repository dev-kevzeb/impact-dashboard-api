<?php

namespace App\Modules\ProgramState\Controller;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProgramStateResource;
use App\Http\Responses\ApiResponse;
use App\Modules\ProgramState\Service\ProgramStateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PublicProgramStateController extends Controller
{
    private ProgramStateService $programStateService;

    public function __construct(ProgramStateService $programStateService)
    {
        $this->programStateService = $programStateService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->get('search');
            $perPage = (int) $request->get('per_page', 10);

            $states = $this->programStateService->getProgramStatesPaginated($search, $perPage);

            return ApiResponse::success(
                'Program states list successfully obtained',
                200,
                [
                    'program_states' => ProgramStateResource::collection($states),
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
