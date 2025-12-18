<?php

namespace App\Modules\IndicatorType\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndicatorTypeRequest;
use App\Http\Resources\IndicatorTypeResource;
use App\Http\Responses\ApiResponse;
use App\Modules\IndicatorType\Domain\IndicatorType;
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

    public function index(Request $request): JsonResponse
    {
        try{
            $search = $request->get("search");
            $perPage = (int) $request->get("per_page", 10);
        
            $query = IndicatorType::query();
            if( $search ) $query->where("name","like","%". $search ."%");
            $indicatorTypes = $query->paginate($perPage);

            return ApiResponse::success(
                "Indicator Types paginated list successfully uploaded",
                200,
                [
                    'indicator_types' => IndicatorTypeResource::collection($indicatorTypes),
                    'total' => $indicatorTypes->count(),
                    'per_page' => $indicatorTypes->perPage(),
                    'current_page' => $indicatorTypes->currentPage(),
                    'last_page' => $indicatorTypes->lastPage(),
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
                'Indicator Type created successfully',
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
                'Indicator Type found',
                200, 
                new IndicatorTypeResource($indicatorType),
            );
        } catch(RuntimeException $exception) {
            return ApiResponse::notFound('Indicator Type');
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
                'Indicator Type uploaded successfully',
                200,
                new IndicatorTypeResource($indicatorType),
            );
        }catch(RuntimeException $exception) {
            return ApiResponse::notFound('Indicator Type');
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
                'Indicator Type found',
                200,
                new IndicatorTypeResource($indicatorType),
            );
        } catch(RuntimeException $exception) {
            return ApiResponse::notFound('Indicator Type');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch(\Exception $exception) {
            return ApiResponse::error($exception->getMessage(),400);
        }
    }
}