<?php

namespace App\Modules\Measure\Controller;

use App\Http\Controllers\Controller;
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
                'Lista de medidas obtenida exitosamente',
                200,
                [
                    'measures' => MeasureResource::collection($measure),
                    'total' => $measure->count(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
        
    }

    public function show(int $id)
    {
        try {
            $measure = $this->measureService->getMeasureById($id);

            return ApiResponse::success(
                'Medida encontrada',
                200,
                new MeasureResource($measure)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Medida');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function showWithIndicators(int $id)
    {
        try {
            $measure = $this->measureService->getMeasureById($id);

            $measure->load('indicators');

            return ApiResponse::success(
                'Medida con sus indicadores recuperada exitosamente',
                200,
                new MeasureResource($measure)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Medida');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function showWithIndicatorsCount(int $id)
    {
        try {
            $measure = $this->measureService->getMeasureById($id);
            $measure->load('indicators');

            return ApiResponse::success(
                'Lista de medidas con sus indicadores recuperada exitosamente',
                200,
                new MeasureResource($measure)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Medida');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|min:2|max:100',
                'strategic_output_id'=> 'required|integer',
            ]);

            $measure = $this->measureService->createMeasure(
                $request->input('name'),
                $request->input('strategic_output_id'),
                );


            return ApiResponse::created(
                'Medida creada exitosamente',
                new MeasureResource($measure)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Medida');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }


    public function update(Request $request, int $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|min:2|max:100',
                'strategic_output_id'=> 'required|integer',
            ]);

            $measure = $this->measureService->updateMeasure($id, $request->input('name'), $request->input('strategic_output_id'));

            return ApiResponse::success(
                'Medida actualziada exitosamente',
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
                'Indicador encontrado exitosamente',
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
                'Indicador agregado exitosamente a la medida',
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
        
        $measure = $this->measureService->findMeasureByName($request->input('measure_id'));

        return ApiResponse::success(
            'Indicador removido exitosamente',
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
                'Medida encontrada',
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
