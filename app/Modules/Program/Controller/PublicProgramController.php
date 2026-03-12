<?php

namespace App\Modules\Program\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\ProgramRequest;
use App\Http\Resources\ProgramResource;
use App\Http\Responses\ApiResponse;
use App\Modules\Program\Service\ProgramService;

class PublicProgramController extends Controller
{
	private ProgramService $programService;

	public function __construct(ProgramService $programService)
	{
		$this->programService = $programService;
	}

	public function index(ProgramRequest $request)
	{
		try {
			$search = $request->get('search');
			$perPage = (int) $request->get('per_page', 10);
			$sort = $request->get('sort', 'date_newest');
			$validated = $request->validated();

			$programs = $this->programService->getPublicPrograms($validated, $search, $perPage, $sort);

			return ApiResponse::success(
				'Programs paginated list successfully uploaded',
				200,
				[
					'programs' => ProgramResource::collection($programs),
					'total' => $programs->count(),
					'per_page' => $programs->perPage(),
					'current_page' => $programs->currentPage(),
					'last_page' => $programs->lastPage(),
					'all' => $programs->total(),
				]
			);
		} catch (\RuntimeException $e) {
			return ApiResponse::error($e->getMessage(), 500);
		} catch (\Exception $e) {
			return ApiResponse::error('Internal server error', 500);
		}
	}

	public function show(int $id)
	{
		try {
			$program = $this->programService->getProgramById($id);

			return ApiResponse::success(
				'Program Found',
				200,
				new ProgramResource($program)
			);
		} catch (\RuntimeException $e) {
			return ApiResponse::notFound('Program');
		} catch (\Exception $e) {
			return ApiResponse::error('Internal server error', 500);
		}
	}
}
