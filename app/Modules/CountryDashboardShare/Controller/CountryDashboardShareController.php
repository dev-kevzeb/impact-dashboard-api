<?php

namespace App\Modules\CountryDashboardShare\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\CountryDashboardShareRequest;
use App\Http\Resources\CountryDashboardShareResource;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Modules\CountryDashboardShare\Service\CountryDashboardShareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CountryDashboardShareController extends Controller
{
    public function __construct(private CountryDashboardShareService $service)
    {
    }

    public function indexMyShares(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 10);
            $shares = $this->service->getSharesCreatedByAuthenticatedCountryManager($perPage);

            return ApiResponse::success('Country dashboard shares retrieved successfully', 200, [
                'shares' => CountryDashboardShareResource::collection($shares),
                'total' => $shares->total(),
                'per_page' => $shares->perPage(),
                'current_page' => $shares->currentPage(),
                'last_page' => $shares->lastPage(),
            ]);
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function indexAdminCandidates(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 10);
            $admins = $this->service->getShareableAdminsForAuthenticatedCountryManager($perPage);

            return ApiResponse::success('Admin share candidates retrieved successfully', 200, [
                'users' => UserResource::collection($admins),
                'total' => $admins->total(),
                'per_page' => $admins->perPage(),
                'current_page' => $admins->currentPage(),
                'last_page' => $admins->lastPage(),
            ]);
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function indexVisibleForAdmin(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 10);
            $shares = $this->service->getVisibleSharesForAuthenticatedAdmin($perPage);

            return ApiResponse::success('Visible country dashboards retrieved successfully', 200, [
                'shares' => CountryDashboardShareResource::collection($shares),
                'total' => $shares->total(),
                'per_page' => $shares->perPage(),
                'current_page' => $shares->currentPage(),
                'last_page' => $shares->lastPage(),
            ]);
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function store(CountryDashboardShareRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $share = $this->service->createShare(
                (int) $validated['country_id'],
                (int) $validated['shared_user_role_id']
            );

            return ApiResponse::created('Country dashboard shared successfully', new CountryDashboardShareResource($share));
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteShare($id);

            return ApiResponse::success('Country dashboard share revoked successfully', 200);
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}
