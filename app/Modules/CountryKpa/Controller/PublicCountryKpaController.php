<?php

namespace App\Modules\CountryKpa\Controller;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Modules\CountryKpa\Service\CountryKpaService;
use Illuminate\Http\Request;
use RuntimeException;

class PublicCountryKpaController extends Controller
{
    protected CountryKpaService $service;

	public function __construct(CountryKpaService $service)
	{
		$this->service = $service;
	}

    public function getAllByCountryId(Request $request, int $countryId)
    {
        try {
            $search  = $request->query('search');
            $perPage = (int) $request->query('per_page', 10);

            $kpas = $this->service->getKpasByCountryPaginated($countryId, $search, $perPage);

            return ApiResponse::success(
                'KPAs by country',
                200,
                [
                    'kpas'=> $kpas->items(),
                    'total' => $kpas->count(),
                    'per_page' => $kpas->perPage(),
                    'current_page' => $kpas->currentPage(),
                    'last_page' => $kpas->lastPage(),
                ]
            );

        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}