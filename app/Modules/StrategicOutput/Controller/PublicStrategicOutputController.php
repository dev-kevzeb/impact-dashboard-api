<?php

namespace App\Modules\StrategicOutput\Controller;

use App\Http\Controllers\Controller;
use App\Http\Resources\StrategicOutputResource;
use App\Http\Responses\ApiResponse;
use App\Modules\StrategicOutput\Service\StrategicOutputService;
use Exception;
use Illuminate\Http\Request;
use RuntimeException;

class PublicStrategicOutputController extends Controller
{
    private StrategicOutputService $strategicOutputService;

    public function __construct(StrategicOutputService $strategicOutputService)
    {
        $this->strategicOutputService = $strategicOutputService;
    }

    public function getStrategicOutputsByKpaId(Request $request, int $kpaId){
        try {
            $search = $request->get("search");
            $perPage = (int) $request->get("per_page", 10);

            $strategic_outputs = $this->strategicOutputService->getStrategicOutputsByKpaId( $perPage, $search, $kpaId);

            return ApiResponse::success(
                'Strategic Outputs paginated list successfully uploaded',
                200,
                [
                    'strategic_outputs' => StrategicOutputResource::collection($strategic_outputs),
                    'total' => $strategic_outputs->count(),
                    'per_page' => $strategic_outputs->perPage(),
                    'current_page' => $strategic_outputs->currentPage(),
                    'last_page' => $strategic_outputs->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }

    }

    public function index(Request $request)
    {
        try {
            $search  = $request->get("search");
            $perPage = (int) $request->get("per_page", 10);
            $countryId = $request->has('country') ? (int) $request->get('country') : null;

            $strategic_outputs = $this->strategicOutputService->getStrategicOutputsForPublic($countryId, $perPage, $search);

            return ApiResponse::success(
                'Strategic Outputs paginated list successfully uploaded',
                200,
                [
                    'strategic_outputs' => StrategicOutputResource::collection($strategic_outputs),
                    'total' => $strategic_outputs->count(),
                    'per_page' => $strategic_outputs->perPage(),
                    'current_page' => $strategic_outputs->currentPage(),
                    'last_page' => $strategic_outputs->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}