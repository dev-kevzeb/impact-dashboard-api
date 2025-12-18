<?php

namespace App\Modules\Beneficiary\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\BeneficiaryRequest;
use App\Modules\Beneficiary\Service\BeneficiaryService;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\BeneficiaryResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class BeneficiaryController extends Controller
{
    private BeneficiaryService $beneficiaryService;

    public function __construct(BeneficiaryService $beneficiaryService)
    {
        $this->beneficiaryService = $beneficiaryService;
    }

    public function index(): JsonResponse
    {
        try {
            $beneficiaries = $this->beneficiaryService->getAllBeneficiaries();
            
            return ApiResponse::success(
                'Beneficiary list successfully obtained',
                200,
                [
                    'beneficiaries' => BeneficiaryResource::collection($beneficiaries),
                    'total' => $beneficiaries->count()
                ]
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function store(BeneficiaryRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $beneficiary = $this->beneficiaryService->createBeneficiary($validated['name']);

            return ApiResponse::created(
                'Beneficiary created successfully',
                new BeneficiaryResource($beneficiary)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $beneficiary = $this->beneficiaryService->getBeneficiaryById($id);

            return ApiResponse::success(
                'Beneficiary found',
                200,
                new BeneficiaryResource($beneficiary)
            );
        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Beneficiary');
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }

    public function update(BeneficiaryRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $beneficiary = $this->beneficiaryService->updateBeneficiary($id, $validated['name']);

            return ApiResponse::success(
                'Beneficiary uploaded successfully',
                200,
                new BeneficiaryResource($beneficiary)
            );

        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Not found')) {
                return ApiResponse::notFound('Beneficiary');
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

            $beneficiary = $this->beneficiaryService->findBeneficiaryByName($request->input('name'));

            return ApiResponse::success(
                'Beneficiary found',
                200,
                new BeneficiaryResource($beneficiary)
            );

        } catch (RuntimeException $e) {
            return ApiResponse::notFound('Beneficiary');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Internal server error', 500);
        }
    }
}
