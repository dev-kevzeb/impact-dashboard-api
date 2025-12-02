<?php

namespace App\Modules\IndicatorType\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndicatorTypeRequest;
use App\Http\Resources\IndicatorTypeResource;
use App\Http\Responses\ApiResponse;
use App\Modules\IndicatorType\Service\IndicatorTypeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class IndicatorTypeController extends Controller
{
    private IndicatorTypeService $indicatorTypeService;

    public function __construct(IndicatorTypeService $indicatorTypeService)
    {
        $this->indicatorTypeService = $indicatorTypeService;
    }

    public function index(): JsonResponse
    {
        try{
            $indicatorTypes = $this->indicatorTypeService->getAllIndicatorTypes();
            return ApiResponse::success(
                "Lista de tipos de indicator obtenida exitosamente",
                200,
                [
                    'indicator_types' => IndicatorTypeResource::collection($indicatorTypes),
                    'total' => $indicatorTypes->count(),
                ]
            );
        } catch(RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), 500);
        } catch (\Exception $exception) {
            return ApiResponse::error($exception->getMessage(),500);
        }
    }

    public function store(IndicatorTypeRequest $request): JsonResponse
    {
        try{
            $validated = $request->validated();
            $indicatorType = $this->indicatorTypeService->createIndicatorType($validated['name']);
            return ApiResponse::created(
                'Tipo de indicador creado exitosamente',
                new IndicatorTypeResource($indicatorType),

            );
        } catch(RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(),400);
        }
    }

    public function show(int $id): JsonResponse
    {
        try{
            $indicatorType = $this->indicatorTypeService->getIndicatorTypeById($id);
            return ApiResponse::success(
                'Tipo de indicador encontrado',
                200, 
                new IndicatorTypeResource($indicatorType),
            );
        } catch(RuntimeException $exception) {
            return ApiResponse::notFound('Tipo de indicador');
        } catch(\Exception $exception) {
            return ApiResponse::error($exception->getMessage(),400);
        }
    }

    public function update(IndicatorTypeRequest $request, int $id): JsonResponse
    {
        try{
            $validated = $request->validated();

            $indicatorType = $this->indicatorTypeService->updateIndicatorType($id, $validated['name']);

            return ApiResponse::success(
                'Tipo de indicador actualizado exitosamente',
                200,
                new IndicatorTypeResource($indicatorType),
            );
        }catch(RuntimeException $exception) {
            return ApiResponse::notFound('Tipo de indicador');
        } catch(\Exception $exception) {
            return ApiResponse::error($exception->getMessage(),400);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try{
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $indicatorType = $this->indicatorTypeService->findIndicatorTypeByName($request->input('name'));

            return ApiResponse::success(
                'Tipo de indicador encontrado',
                200,
                new IndicatorTypeResource($indicatorType),
            );
        } catch(RuntimeException $exception) {
            return ApiResponse::notFound('Tipo de Indicador');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch(\Exception $exception) {
            return ApiResponse::error($exception->getMessage(),400);
        }
    }
}