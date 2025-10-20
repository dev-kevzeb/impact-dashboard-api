<?php

namespace App\Http\Controllers;

use App\Services\KpaService;
use Illuminate\Http\Request;

class KpaController extends Controller
{
    protected KpaService $service;

    public function __construct(KpaService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json($this->service->getAllKpas());
    }

    public function show($id)
    {
        return response()->json($this->service->getKpaById((int)$id));
    }

    public function store(Request $request)
    {
        $data = $request->only(['name','implementation']);
        $kpa = $this->service->createKpa($data);
        return response()->json($kpa, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->only(['name','implementation']);
        $kpa = $this->service->updateKpa((int)$id, $data);
        return response()->json($kpa);
    }

    public function destroy($id)
    {
        $this->service->deleteKpa((int)$id);
        return response()->json(null, 204);
    }
}
