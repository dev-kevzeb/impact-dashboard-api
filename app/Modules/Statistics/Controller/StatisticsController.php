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

    /**
     * @OA\Get(
     *     path="/statistics/measures/{measureId}/implementation",
     *     tags={"Statistics"},
     *     summary="Get measure implementation",
     *     description="Retrieves the implementation percentage or data for a specific measure",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="measureId",
     *         in="path",
     *         required=true,
     *         description="Measure ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Measure implementation retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Measure implementation retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/statistics/strategic-outputs/{strategicOutputId}/implementation",
     *     tags={"Statistics"},
     *     summary="Get strategic output implementation",
     *     description="Retrieves the implementation data for a strategic output",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="strategicOutputId",
     *         in="path",
     *         required=true,
     *         description="Strategic Output ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Strategic Output implementation retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Strategic Output implementation retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/statistics/kpas/{kpaId}/implementation",
     *     tags={"Statistics"},
     *     summary="Get KPA implementation",
     *     description="Retrieves implementation data for a specific KPA",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="kpaId",
     *         in="path",
     *         required=true,
     *         description="KPA ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="KPA implementation retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="KPA implementation retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/statistics/overall/implementation",
     *     tags={"Statistics"},
     *     summary="Get overall implementation",
     *     description="Retrieves overall implementation across all KPAs",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Overall implementation retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Overall implementation retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/statistics/kpas/implementation",
     *     tags={"Statistics"},
     *     summary="Get all KPAs implementation",
     *     description="Retrieves implementation data for all KPAs",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="All KPAs implementation retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="All KPAs implementation retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/statistics/countries/{countryId}/overall/implementation",
     *     tags={"Statistics"},
     *     summary="Get country overall implementation",
     *     description="Retrieves overall implementation for a specific country",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="countryId",
     *         in="path",
     *         required=true,
     *         description="Country ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Country overall implementation retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Country overall implementation retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/statistics/countries/{countryId}/kpas/implementation",
     *     tags={"Statistics"},
     *     summary="Get country KPAs implementation",
     *     description="Retrieves implementation data of all KPAs for a specific country",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="countryId",
     *         in="path",
     *         required=true,
     *         description="Country ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Country all KPAs implementation retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Country all KPAs implementation retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
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