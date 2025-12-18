<?php
namespace App\Modules\StrategicOutput\Controller;

use App\Http\Requests\StrategicOutputRequest;
use App\Http\Resources\StrategicOutputResource;
use App\Modules\Measure\Domain\Measure;
use App\Modules\Measure\Service\MeasureService;
use App\Modules\StrategicOutput\Service\StrategicOutputService;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use RuntimeException;

class StrategicOutputController extends Controller
{
    private StrategicOutputService $strategicOutputService;
    private MeasureService $measureService;

    public function __construct(StrategicOutputService $strategicOutputService, MeasureService $measureService)
    {
        $this->strategicOutputService = $strategicOutputService;
        $this->measureService = $measureService;
    }

    public function index()
    {
        try {
            $strategicOutputs = $this->strategicOutputService->getAllStrategicOutputs();
            $strategicOutputs->load(['countryKpa.country', 'countryKpa.kpa', 'measures']);

            return ApiResponse::success(
                'List of strategic outputs successfully obtained',
                200,
                [
                    'strategic_outputs' => StrategicOutputResource::collection($strategicOutputs),
                    'total' => $strategicOutputs->count()
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function show($id)
    {
        try {
            $strategicOutput = $this->strategicOutputService->getStrategicOutputById($id);
            
            $strategicOutput->load(['measures']);
            
            return ApiResponse::success(
                'Strategic Output found',
                200,
                new StrategicOutputResource($strategicOutput)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Strategic output');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function showByCountryKpa($id)
    {
        try {
            $strategicOutputs = $this->strategicOutputService->getByCountryKpaId($id);

            return ApiResponse::success(
                'Strategic outputs for CountryKpa obtained correctly',
                200,
                StrategicOutputResource::collection($strategicOutputs)
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);statusCode: 
        }
    }


    public function store(StrategicOutputRequest $request)
    {
        $validated = $request->validated();

        try {
            $strategicOutput = $this->strategicOutputService->createStrategicOutput(
                $validated['name'],
                $validated['id_ck']
            );
            
            $strategicOutput->load(['countryKpa.country', 'countryKpa.kpa', 'measures']);
            
            return ApiResponse::created(
                'Successfully created strategic result',
                new StrategicOutputResource($strategicOutput)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function update(StrategicOutputRequest $request, $id)
    {
        $validated = $request->validated();

        try {
            $strategicOutput = $this->strategicOutputService->updateStrategicOutput(
                $id,
                $validated['name'],
                $validated['id_ck'] ?? null
            );
            
            $strategicOutput->load(['countryKpa.country', 'countryKpa.kpa', 'measures']);
            
            return ApiResponse::success(
                'Strategic Output uploaded successfully',
                200,
                new StrategicOutputResource($strategicOutput)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function addMeasure(Request $request)
    {
        try {
            $request->validate([
                'strategic_output_id' => 'required|integer',
                'name' => 'required|string|min:2|max:100',
            ]);

            $strategicOutput = $this->strategicOutputService->getStrategicOutputById($request->input('strategic_output_id'));

            $measure = Measure::at($request->input('name'), $strategicOutput);
            $strategicOutput->addMeasure($measure);

            $strategicOutput->load('measures');

            return ApiResponse::success(
                'Measure successfully added to strategic output',
                200,
                $strategicOutput
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    public function removeMeasure(Request $request)
    {
        try {
            $request->validate([
                'strategic_output_id' => 'required|integer',
                'measure_id' => 'required|integer'
            ]);

            $strategicOutput = $this->strategicOutputService
                ->getStrategicOutputById($request->input('strategic_output_id'));

            $measure = $this->measureService->getMeasureById($request->input('measure_id'));
            if ($measure->strategic_output_id !== $strategicOutput->id) throw new RuntimeException('The measure does not belong to this strategic output');

            $measure->delete();

            $strategicOutput->load('measures');

            return ApiResponse::success(
                'Measure successfully removed from strategic output',
                200,
                $strategicOutput
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }
    public function search(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $strategicOutput = $this->strategicOutputService
                ->findStrategicOutputByName($request->input('name'));

            $strategicOutput->load(['countryKpa.country', 'countryKpa.kpa', 'measures']);

            return ApiResponse::success(
                'Strategic Output found',
                200,
                new StrategicOutputResource($strategicOutput)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }



}
