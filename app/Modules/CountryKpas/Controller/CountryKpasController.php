<?php

namespace App\Modules\CountryKpas\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\CountryKpas\Service\CountryKpasService;

class CountryKpasController extends Controller
{
	protected CountryKpasService $service;

	public function __construct(CountryKpasService $service)
	{
		$this->service = $service;
	}

	public function index()
	{
		return response()->json($this->service->list());
	}

	public function show($id)
	{
		return response()->json($this->service->get((int)$id));
	}

	public function store(Request $request)
	{
		$data = $request->only(['id_country','id_kpa']);
		return response()->json($this->service->create($data), 201);
	}

	public function destroy($id)
	{
		return response()->json($this->service->delete((int)$id));
	}

	public function attach(Request $request)
	{
		$data = $request->only(['id_country','id_kpa']);
		return response()->json($this->service->attach((int)$data['id_country'], (int)$data['id_kpa']));
	}

	public function detach(Request $request)
	{
		$data = $request->only(['id_country','id_kpa']);
		return response()->json($this->service->detach((int)$data['id_country'], (int)$data['id_kpa']));
	}
}

