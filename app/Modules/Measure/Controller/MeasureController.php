<?php

namespace App\Modules\Measure\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\MeasureRequest;
use App\Http\Resources\MeasureResource;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

use App\Modules\IndicatorType\Service\IndicatorTypeService;
use App\Modules\Indicator\Service\IndicatorService;
use App\Modules\Measure\Service\MeasureService;

use Illuminate\Http\Request;
use \App\Modules\Indicator\Domain\Indicator;
use RuntimeException;

class MeasureController extends Controller
{
    private MeasureService $measureService;
    private IndicatorTypeService $indicatorTypeService;
    private IndicatorService $indicatorService;

    public function __construct(MeasureService $measureService, IndicatorTypeService $indicatorTypeService, IndicatorService $indicatorService)
    {
        $this->measureService = $measureService;
        $this->indicatorTypeService = $indicatorTypeService;
        $this->indicatorService = $indicatorService;
    }

    public function index()
    {
        try{
            $measure = $this->measureService->getAllMeasures();

            return ApiResponse::success(
                'List of measure successfully obtained',
                200,
                [
                    'measures' => MeasureResource::collection($measure),
                    'total' => $measure->count(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal Server Error', 500);
        }
    }

    public function listByStrategicOutput(int $id)
    {
        try {
            $measures = $this->measureService->getMeasuresByStrategicOutputId($id);

            return ApiResponse::success(
                'Measure obtained correctly',
                200,
                MeasureResource::collection($measures)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal Server Error', 500);
        }
    }

    public function show(int $id)
    {
        try {
            $measure = $this->measureService->getMeasureById($id);

            return ApiResponse::success(
                'Measure found',
                200,
                new MeasureResource($measure)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Measure');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function showWithIndicators(int $id)
    {
        try {
            $measure = $this->measureService->getMeasureById($id);

            $measure->load('indicators');

            return ApiResponse::success(
                'Measure with its indicators successfully recovered',
                200,
                new MeasureResource($measure)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Measure');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function showWithIndicatorsCount(int $id)
    {
        try {
            $measure = $this->measureService->getMeasureById($id);
            $measure->load('indicators');

            return ApiResponse::success(
                'List of measures with their indicators successfully recovered',
                200,
                new MeasureResource($measure)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Measure');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server Error', 500);
        }
    }

    public function store(MeasureRequest $request)
    {
        try {
            $validated = $request->validated();

            $measure = $this->measureService->createMeasure(
                $validated['name'],
                $validated['strategic_output_id'],
                );


            return ApiResponse::created(
                'Measure created successfully',
                new MeasureResource($measure)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Measure');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }


    public function update(MeasureRequest $request, int $id)
    {
        try {

            $validated = $request->validated();

            $measure = $this->measureService->updateMeasure($id, $validated['name'], $validated['strategic_output_id']);

            return ApiResponse::success(
                'Measure uploaded successfully',
                200,
                new MeasureResource($measure)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    public function getIndicatorByName(int $id, string $indicatorName)
    {
        try {
            $indicator = $this->measureService->getIndicatorOfMeasureByName($id, $indicatorName);

            return ApiResponse::success(
                'Indicator found successfully',
                200,
                ['indicator' => $indicator]
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    public function addIndicator(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|min:2|max:100',
                'measure_id' => 'required|integer',
                'target'=>'required|numeric',
                'type_id' => 'required|integer'
            ]);

            $measure = $this->measureService->getMeasureById($request->input('measure_id'));
            $indicatorType = $this->indicatorTypeService->getIndicatorTypeById($request->input('type_id'));

            $indicator = Indicator::at($request->input('name'), $indicatorType, $request->input('target'), $measure);
            $measure->addIndicator($indicator);
            $measure->load('indicators');

            return ApiResponse::success(
                'Indicator successfully added to the measure',
                200,
                new MeasureResource($measure)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    public function removeIndicator(Request $request)
    {
        $request->validate([
            'measure_id' => 'required|integer',
            'indicator_id' => 'required|integer'
        ]);

        $indicator = $this->indicatorService->getIndicatorById($request->input('indicator_id'));
        $this->measureService->removeIndicatorFromMeasure($request->input('measure_id'), $indicator);
        
        $measure = $this->measureService->getMeasureById($request->input('measure_id'));

        return ApiResponse::success(
            'Indicator successfully removed',
            200,
            new MeasureResource($measure)
        );
    }

    public function search(Request $request): JsonResponse
    {
        try{
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $measure = $this->measureService->findMeasureByName($request->input('name'));

            return ApiResponse::success(
                'Measure found',
                200,
                ['measure' => new MeasureResource($measure)]
            );

        }catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

}
