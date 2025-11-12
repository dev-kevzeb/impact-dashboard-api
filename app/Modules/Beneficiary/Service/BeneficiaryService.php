<?php

namespace App\Modules\Beneficiary\Service;

use App\Modules\Beneficiary\Domain\Beneficiary;
use App\Modules\Beneficiary\Repository\BeneficiaryRepository;
use RuntimeException;

class BeneficiaryService
{
    private BeneficiaryRepository $beneficiaryRepository;

    public function __construct(BeneficiaryRepository $beneficiaryRepository)
    {
        $this->beneficiaryRepository = $beneficiaryRepository;
    }

    public function createBeneficiary(string $name): Beneficiary
    {
        $beneficiary = Beneficiary::at($name);
        
        $this->beneficiaryRepository->save($beneficiary);
        
        return $beneficiary;
    }

    public function getBeneficiaryById(int $id): Beneficiary
    {
        return $this->beneficiaryRepository->findById($id);
    }

    public function findBeneficiaryByName(string $name): Beneficiary
    {
        return $this->beneficiaryRepository->findBy('name', $name);
    }

    public function getAllBeneficiaries()
    {
        return $this->beneficiaryRepository->getAll();
    }

    public function updateBeneficiary(int $id, string $name): Beneficiary
    {
        $beneficiary = $this->beneficiaryRepository->findById($id);
        $updatedBeneficiary = Beneficiary::at($name);
        $beneficiary->name = $updatedBeneficiary->name;
        
        $this->beneficiaryRepository->save($beneficiary);
        
        return $beneficiary;
    }

    public function beneficiaryExists(string $name): bool
    {
        return $this->beneficiaryRepository->exists('name', $name);
    }
}
