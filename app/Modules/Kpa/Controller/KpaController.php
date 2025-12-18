<?php

namespace App\Modules\Kpa\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\KpaRequest;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\KpaResource;
use App\Modules\Kpa\Domain\Kpa;
use App\Modules\Kpa\Service\KpaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class KpaController extends Controller
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

            $query = Kpa::query();

            if( $search ) $query->where("name","like","%". $search ."%");

            $kpas = $query->paginate($perPage);
            
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
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $kpa = $this->kpaService->getKpaById($id);

            return ApiResponse::success(
                'KPA found',
                200,
                new KpaResource($kpa)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('KPA');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function store(KpaRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $kpa = $this->kpaService->createKpa(
                $validated['name'],
                (float) $validated['implementation']
            );

            return ApiResponse::created(
                'KPA created successfully',
                new KpaResource($kpa)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }


    public function update(KpaRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $kpa = $this->kpaService->updateKpa(
                $id,
                $validated['name'],
                (float) $validated['implementation']
            );

            return ApiResponse::success(
                'KPA uploaded successfully',
                200,
                new KpaResource($kpa)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $kpa = $this->kpaService->findKpaByName($request->input('name'));

            return ApiResponse::success(
                'KPA found',
                200,
                new KpaResource($kpa)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::notFound('KPA');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
