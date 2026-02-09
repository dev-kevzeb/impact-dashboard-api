<?php

namespace App\Modules\Stats\Controller;

use App\Http\Controllers\Controller;
use App\Modules\Stats\Service\DashboardStatsService;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    private DashboardStatsService $dashboardService;

    public function __construct(DashboardStatsService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function getDashboardStats(): JsonResponse
    {
        try {
            $stats = $this->dashboardService->getStats();

            return ApiResponse::success(
                'Dashboard statistics retrieved successfully',
                200,
                $stats
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function getProgramsByState(): JsonResponse
    {
        try {
            $data = $this->dashboardService->getProgramsByState();

            return ApiResponse::success(
                'Programs by state retrieved successfully',
                200,
                $data
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function getProjectsByState(): JsonResponse
    {
        try {
            $data = $this->dashboardService->getProjectsByState();

            return ApiResponse::success(
                'Projects by state retrieved successfully',
                200,
                $data
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function getProjectsPerProgram(): JsonResponse
    {
        try {
            $data = $this->dashboardService->getProjectsPerProgram();

            return ApiResponse::success(
                'Projects per program retrieved successfully',
                200,
                $data
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function getProjectsTimeline(): JsonResponse
    {
        try {
            $data = $this->dashboardService->getProjectsTimeline();

            return ApiResponse::success(
                'Projects timeline retrieved successfully',
                200,
                $data
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function getProjectsProgress(): JsonResponse
    {
        try {
            $data = $this->dashboardService->getProjectsProgress();

            return ApiResponse::success(
                'Projects progress distribution retrieved successfully',
                200,
                $data
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
