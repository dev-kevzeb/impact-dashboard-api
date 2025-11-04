<?php
namespace App\Modules\StrategicOutput\Controller;

use App\Modules\StrategicOutput\Service\StrategicOutputService;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use RuntimeException;

class StrategicOutputController extends Controller
{
    private StrategicOutputService $strategicOutputService;

    public function __construct(StrategicOutputService $strategicOutputService)
    {
        $this->strategicOutputService = $strategicOutputService;
    }

    public function index()
    {
        try {
            $strategicOutputs = $this->strategicOutputService->getAllStrategicOutputs();
            
            // Cargar relaciones con country_kpa, kpa y country
            $strategicOutputs->load(['countryKpa.country', 'countryKpa.kpa']);
            
            return ApiResponse::success(
                'Lista de resultados estratégicos obtenida exitosamente',
                200,
                [
                    'strategic_outputs' => $strategicOutputs,
                    'total' => $strategicOutputs->count()
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function show($id)
    {
        try {
            $strategicOutput = $this->strategicOutputService->getStrategicOutputById($id);
            
            $strategicOutput->load(['countryKpa.country', 'countryKpa.kpa']);
            
            return ApiResponse::success(
                'Resultado estratégico encontrado',
                200,
                $strategicOutput
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Resultado estratégico');
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'id_ck' => 'required|integer|exists:country_kpa,id',
        ]);

        try {
            $strategicOutput = $this->strategicOutputService->createStrategicOutput(
                $request->input('name'),
                $request->input('id_ck')
            );
            
            $strategicOutput->load(['countryKpa.country', 'countryKpa.kpa']);
            
            return ApiResponse::success(
                'Registro creado',
                201,
                $strategicOutput
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'id_ck' => 'sometimes|integer|exists:country_kpa,id',
        ]);

        try {
            $strategicOutput = $this->strategicOutputService->updateStrategicOutput(
                $id,
                $request->input('name'),
                $request->input('id_ck')
            );
            
            // Cargar relaciones con country_kpa, kpa y country
            $strategicOutput->load(['countryKpa.country', 'countryKpa.kpa']);
            
            return ApiResponse::success(
                'Resultado estratégico actualizado exitosamente',
                200,
                $strategicOutput
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }
}
