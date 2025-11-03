<?php

namespace App\Modules\Country\Controller;

use App\Http\Controllers\Controller;
use App\Modules\Country\Service\CountryService;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    protected CountryService $service;

    public function __construct(CountryService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json($this->service->getAllCountries());
    }

    public function show($id)
    {
        return response()->json($this->service->getCountryById((int)$id));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->only(['name','currency_id']);
            $country = $this->service->createCountry($data);
            return response()->json([
                'success' => true,
                'message' => 'País creado exitosamente',
                'data' => $country
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el país',
                'data' => []
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->only(['name','currency_id']);
            $country = $this->service->updateCountry((int)$id, $data);
            return response()->json([
                'success' => true,
                'message' => 'País actualizado exitosamente',
                'data' => $country
            ], 200);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el país',
                'data' => []
            ], 500);
        }
    }

    public function destroy($id)
    {
        $this->service->deleteCountry((int)$id);
        return response()->json(null, 204);
    }
}
