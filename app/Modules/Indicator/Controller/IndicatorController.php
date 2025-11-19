<?php

namespace App\Modules\Indicator\Controller;

use App\Http\Controllers\Controller;
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
                'Lista de Indicadores obtenida exitosamente',
                200,
                [
                    'indicators' => IndicatorResource::collection($indicators),
                    'total' => $indicators->count(),
                ]
            );
        }  catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try{
            $indicator = $this->indicatorService->getIndicatorById($id);

            return ApiResponse::success(
                'Indicador encontrado',
                200,
                new IndicatorResource($indicator)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Indicador');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try{
            $request->validate([
                'name' => 'required|string',
                'target'=>'required|numeric',
                'type_id' => 'required|integer',
                'measure_id' => 'required|integer'
            ]);

            $indicator = $this->indicatorService->createIndicator(
                $request->input('name'),
                $request->input('target'),
                $request->input('type_id'),
                $request->input('measure_id'),
            );

            return ApiResponse::created(
                'Indicador creado exitosamente',
                new IndicatorResource($indicator)
            );

        }catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try{
            $request->validate([
                'name' => 'required|string',
                'target'=>'required|numeric',
                'type_id' => 'required|integer',
                'measure_id' => 'required|integer'
            ]);

            $indicator = $this->indicatorService->updateIndicator(
                $id,
                $request->input('name'),
                $request->input('target'),
                $request->input('type_id'),
                $request->input('measure_id'),
            );

            return ApiResponse::success(
                    'Indicador actualizado exitosamente',
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

            $indicador = $this->indicatorService->findIndicatorByName($request->input('name'));

            return ApiResponse::success(
                'Indicador encontrado',
                200,
                new IndicatorResource($indicador)
            );
        }catch (RuntimeException $e) {
            return ApiResponse::notFound('Indicador');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }
}