<?php

namespace App\Modules\Kpa\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\KpaRequest;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\KpaResource;
use App\Modules\Kpa\Service\KpaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;


class PublicKpaController extends Controller
{
    private KpaService $kpaService;

    public function __construct(KpaService $kpaService)
    {
        $this->kpaService = $kpaService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->get("search");
            $perPage = (int) $request->get("per_page", 10);

            $kpas = $this->kpaService->getKpasPaginated($search, $perPage);
            
            return ApiResponse::success(
                'KPAs paginated list successfully uploaded',
                200,
                [
                    'kpas' => KpaResource::collection($kpas),
                    'total' => $kpas->count(),
                    'per_page' => $kpas->perPage(),
                    'current_page' => $kpas->currentPage(),
                    'last_page' => $kpas->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}