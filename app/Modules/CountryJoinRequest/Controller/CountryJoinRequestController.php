<?php

namespace App\Modules\CountryJoinRequest\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\CountryJoinRequestDecisionRequest;
use App\Http\Requests\CountryJoinRequestRequest;
use App\Http\Resources\CountryJoinRequestResource;
use App\Http\Responses\ApiResponse;
use App\Modules\CountryJoinRequest\Service\CountryJoinRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CountryJoinRequestController extends Controller
{
    private CountryJoinRequestService $service;

    public function __construct(CountryJoinRequestService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 10);
            $status = $request->get('status');
            $countryId = $request->get('country_id') ? (int) $request->get('country_id') : null;
            $search = $request->get('search');

            $requests = $this->service->listRequests($perPage, $status, $countryId, $search);

            return ApiResponse::success('Country join requests retrieved successfully', 200, [
                'requests' => CountryJoinRequestResource::collection($requests),
                'total' => $requests->total(),
                'per_page' => $requests->perPage(),
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
            ]);
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function store(CountryJoinRequestRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $joinRequest = $this->service->createRequest((int) $validated['country_id']);

            return ApiResponse::created('Country join request submitted successfully', new CountryJoinRequestResource($joinRequest));
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function search(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $joinRequest = $this->service->getRequestById($id);

            return ApiResponse::success('Country join request retrieved successfully', 200, new CountryJoinRequestResource($joinRequest));
        } catch (RuntimeException $e) {
            if (str_contains(strtolower($e->getMessage()), 'not found')) {
                return ApiResponse::notFound('CountryJoinRequest');
            }

            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function update(int $id, CountryJoinRequestDecisionRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $joinRequest = $this->service->reviewRequest(
                $id,
                (string) $validated['action']
            );

            return ApiResponse::success('Country join request updated successfully', 200, new CountryJoinRequestResource($joinRequest));
        } catch (RuntimeException $e) {
            if (str_contains(strtolower($e->getMessage()), 'not found')) {
                return ApiResponse::notFound('CountryJoinRequest');
            }

            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}
