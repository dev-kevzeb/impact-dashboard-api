<?php

namespace App\Modules\Donor\Controller;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\DonorResource;
use App\Http\Requests\DonorRequest;
use App\Modules\Donor\Service\DonorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class DonorController extends Controller
{
    private DonorService $donorService;

    public function __construct(DonorService $donorService)
    {
        $this->donorService = $donorService;
    }
    public function index(): JsonResponse
    {
        try {
            $donors = $this->donorService->getAllDonors();
            
            return ApiResponse::success(
                'Donors list successfully obtained',
                200,
                [
                    'donors' => DonorResource::collection($donors),
                    'total' => $donors->count()
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $donor = $this->donorService->getDonorById($id);

            return ApiResponse::success(
                'Donor found',
                200,
                new DonorResource($donor)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Donor');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function store(DonorRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $donor = $this->donorService->createDonor($validated['name'], $validated['contribution'], $validated['project_id']);

            return ApiResponse::created(
                'Donor created successfully',
                new DonorResource($donor)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function update(DonorRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $donor = $this->donorService->updateDonor($id, $validated['name'], $validated['contribution'], $validated['project_id']);

            return ApiResponse::success(
                'Donor uploaded successfully',
                200,
                new DonorResource($donor)
            );
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Not Found')) {
                return ApiResponse::notFound('Donor');
            }
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|min:1'
            ]);

            $donor = $this->donorService->findDonorByName($request->input('name'));

            return ApiResponse::success(
                'Donor found',
                200,
                new DonorResource($donor)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Donor');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}