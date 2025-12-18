<?php

namespace App\Modules\Agency\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgencyRequest;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\AgencyResource;
use App\Modules\Agency\Service\AgencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AgencyController extends Controller
{
    private AgencyService $agencyService;

    public function __construct(AgencyService $agencyService)
    {
        $this->agencyService = $agencyService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get("per_page", 10);

            $agencies = $this->agencyService->getAllAgencies($perPage);
            
            return ApiResponse::success(
                'Agencies paginated list successfully uploaded',
                200,
                [
                    'agencies' => AgencyResource::collection($agencies),
                    'total' => $agencies->count(),
                    'per_page' => $agencies->perPage(),
                    'current_page' => $agencies->currentPage(),
                    'last_page' => $agencies->lastPage(),
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
            $agency = $this->agencyService->getAgencyById($id);

            return ApiResponse::success(
                'Agency Found',
                200,
                new AgencyResource($agency)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Agency');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function store(AgencyRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $agency = $this->agencyService->createAgency(
                $validated['name'],
                $validated['url'],
                $validated['is_approved']
            );

            return ApiResponse::created(
                'Agency created successfully',
                new AgencyResource($agency)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    public function update(AgencyRequest $request, int $id): JsonResponse
    {
        try {
            $validated =$request->validated();

            $agency = $this->agencyService->updateAgency(
                $id,
                $validated['name'],
                $validated['url'],
                $validated['is_approved']
            );

            return ApiResponse::success(
                'Agency updated successfully',
                200,
                new AgencyResource($agency)
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

            $agency = $this->agencyService->findAgencyByName($request->input('name'));

            return ApiResponse::success(
                'Agency found',
                200,
                new AgencyResource($agency)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Agency');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
