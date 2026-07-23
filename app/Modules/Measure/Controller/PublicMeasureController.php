<?php

namespace App\Modules\Measure\Controller;

use App\Http\Controllers\Controller;
use App\Http\Resources\MeasureResource;
use App\Http\Responses\ApiResponse;
use App\Modules\Measure\Service\MeasureService;
use Exception;
use Illuminate\Http\Request;
use RuntimeException;

class PublicMeasureController extends Controller
{
    private MeasureService $measureService;

    public function __construct(MeasureService $measureService)
    {
        $this->measureService = $measureService;
    }

    public function getMMeasuresByStrategicOutputId(Request $request, int $id)
    {
        $search = $request->get("search");
        $perPage = (int) $request->get("per_page", 10);

        try {
            $measures = $this->measureService->getAllPaginatedMeasuresByStrategicOutputId($id, $search, $perPage);

            return ApiResponse::success(
                'Measure obtained correctly',
                200,
                [
                    'measures' => MeasureResource::collection($measures),
                    'total' => $measures->count(),
                    'per_page' => $measures->perPage(),
                    'current_page' => $measures->currentPage(),
                    'last_page' => $measures->lastPage(),
                ]
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Exception $e) {
            return ApiResponse::error('Internal Server Error', 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $countryId = (int) $request->get("country");
            $search    = $request->get("search");
            $perPage   = (int) $request->get("per_page", 10);

            if (!$countryId) {
                return ApiResponse::error('Country parameter is required', 400);
            }

            $measures = $this->measureService->getMeasuresForPublic($countryId, $perPage, $search);

            return ApiResponse::success(
                'Measures paginated list successfully uploaded',
                200,
                [
                    'measures' => MeasureResource::collection($measures),
                    'total' => $measures->count(),
                    'per_page' => $measures->perPage(),
                    'current_page' => $measures->currentPage(),
                    'last_page' => $measures->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (Exception $e) {
            return ApiResponse::error('Internal Server Error', 500);
        }
    }
}