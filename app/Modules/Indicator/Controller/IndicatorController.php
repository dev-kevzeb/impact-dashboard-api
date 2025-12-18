<?php

namespace App\Modules\Indicator\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndicatorRequest;
use App\Http\Resources\IndicatorResource;
use App\Http\Responses\ApiResponse;

use App\Modules\Indicator\Service\IndicatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class IndicatorController extends Controller
{
    private IndicatorService $indicatorService;

    public function __construct(IndicatorService $indicatorService)
    {
        $this->indicatorService = $indicatorService;
    }

    public function index(): JsonResponse
    {
        try{
            $indicators = $this->indicatorService->getAllIndicators();

            return ApiResponse::success(
                'List of Indicators successfully obtained',
                200,
                [
                    'indicators' => IndicatorResource::collection($indicators),
                    'total' => $indicators->count(),
                ]
            );
        }  catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try{
            $indicator = $this->indicatorService->getIndicatorById($id);

            return ApiResponse::success(
                'Indicator found',
                200,
                new IndicatorResource($indicator)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Indicator');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function store(IndicatorRequest $request): JsonResponse
    {
        try{
            $validated = $request->validated();

            $indicator = $this->indicatorService->createIndicator(
                $validated['name'],
                $validated['target'],
                $validated['type_id'],
                $validated['measure_id'],
            );

            return ApiResponse::created(
                'Indicator created successfully',
                new IndicatorResource($indicator)
            );

        }catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    public function update(IndicatorRequest $request, int $id): JsonResponse
    {
        try{
            $validated = $request->validated();

            $indicator = $this->indicatorService->updateIndicator(
                $id,
                $validated['name'],
                $validated['target'],
                $validated['type_id'],
                $validated['measure_id'],
            );

            return ApiResponse::success(
                    'Indicator uploaded successfully',
                    200,
                    new IndicatorResource($indicator)
                );

        }catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    public function search(Request $request): JsonResponse
    {
        try{
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $indicator = $this->indicatorService->findIndicatorByName($request->input('name'));

            return ApiResponse::success(
                'Indicator found',
                200,
                new IndicatorResource($indicator)
            );
        }catch (RuntimeException $e) {
            return ApiResponse::notFound('Indicator');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}