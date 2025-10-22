<?php

namespace App\Modules\Currency\Controller;

use App\Http\Controllers\Controller;
use App\Modules\Currency\Service\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    protected CurrencyService $service;

    public function __construct(CurrencyService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json($this->service->getAllCurrencies());
    }

    public function show($id)
    {
        return response()->json($this->service->getCurrencyById((int)$id));
    }

    public function store(Request $request)
    {
        try {
            $currency = $this->service->createCurrency($request->only(['code']));
            return response()->json($currency, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->only(['code']);
        // basic validation: code must be 3 chars

        try {
            $currency = $this->service->updateCurrency((int)$id, $data);
            return response()->json($currency);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar la moneda'], 500);
        }
    }

    public function destroy($id)
    {
        $this->service->deleteCurrency((int)$id);
        return response()->json(null, 204);
    }
}
