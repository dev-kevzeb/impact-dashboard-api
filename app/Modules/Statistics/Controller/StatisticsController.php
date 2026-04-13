<?php

namespace App\Modules\Statistics\Controller;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Modules\Statistics\Service\StatisticsService;
use RuntimeException;

class StatisticsController extends Controller{
    private StatisticsService $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function getMeasureImplementation(int $measureId)
    {
        try {
            $implementation = $this->statisticsService->getMeasureImplementation($measureId);
            return ApiResponse::success(
                'Measure implementation retrieved successfully',
                200,
                $implementation
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getStrategicOutputImplementation(int $strategicOutputId)
    {
        try {
            $implementation = $this->statisticsService->getStrategicOutputImplementation($strategicOutputId);
            return ApiResponse::success(
                'Strategic Output implementation retrieved successfully',
                200,
                $implementation
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getKpaImplementation(int $kpaId)
    {
        try {
            $implementation = $this->statisticsService->getKpaImplementation($kpaId);
            return ApiResponse::success(
                'KPA implementation retrieved successfully',
                200,
                $implementation
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getOverallImplementation()
    {
        try {
            $implementation = $this->statisticsService->getOverallImplementation();
            return ApiResponse::success(
                'Overall implementation retrieved successfully',
                200,
                $implementation
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getAllKpasImplementation()
    {
        try {
            $implementation = $this->statisticsService->getAllKpasImplementation();
            return ApiResponse::success(
                'All KPAs implementation retrieved successfully',
                200,
                $implementation
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getCountryOverallImplementation(int $countryId)
    {
        try {
            $implementation = $this->statisticsService->getCountryOverallImplementation($countryId);
            return ApiResponse::success(
                'Country overall implementation retrieved successfully',
                200,
                $implementation
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getCountryAllKpasImplementation(int $countryId)
    {
        try {
            $implementation = $this->statisticsService->getCountryAllKpasImplementation($countryId);
            return ApiResponse::success(
                'Country all KPAs implementation retrieved successfully',
                200,
                $implementation
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}