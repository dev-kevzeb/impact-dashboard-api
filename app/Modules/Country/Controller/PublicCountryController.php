<?php

namespace App\Modules\Country\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\CountryRequest;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\CountryResource;
use App\Modules\Country\Service\CountryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;


class PublicCountryController extends Controller
{
    private CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get("per_page", 100);

            $countries = $this->countryService->getAllCountriesForDropdown($perPage);

            return ApiResponse::success(
                'Countries paginated list successfully uploaded',
                200,
                [
                    'countries' => CountryResource::collection($countries),
                    'total' => $countries->total(),
                    'per_page' => $countries->perPage(),
                    'current_page' => $countries->currentPage(),
                    'last_page' => $countries->lastPage(),
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
