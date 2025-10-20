<?php

namespace App\Http\Controllers;

use App\Services\CountryService;
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
        $data = $request->only(['name','currency_id']);
        $country = $this->service->createCountry($data);
        return response()->json($country, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->only(['name','currency_id']);
        $country = $this->service->updateCountry((int)$id, $data);
        return response()->json($country);
    }

    public function destroy($id)
    {
        $this->service->deleteCountry((int)$id);
        return response()->json(null, 204);
    }
}
